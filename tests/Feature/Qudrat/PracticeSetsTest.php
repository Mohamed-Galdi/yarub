<?php

namespace Tests\Feature\Qudrat;

use App\Http\Middleware\LogVisit;
use App\Models\QudratCategory;
use App\Models\QudratPracticeSession;
use App\Models\QudratQuestion;
use App\Models\User;
use App\Services\Qudrat\ContentImporter;
use App\Services\Qudrat\ContentValidator;
use App\Services\Qudrat\Practice;
use App\Services\Qudrat\PracticeSets;
use App\Services\Qudrat\Progress;
use Tests\QudratTestCase;

class PracticeSetsTest extends QudratTestCase
{
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        app(ContentImporter::class)->import(app(ContentValidator::class)->validateDirectory(base_path('qudrat_content')));
        $this->student = User::create(['name' => 'Sets', 'email' => 'qudrat-sets@example.invalid', 'password' => 'local', 'role' => 'student']);
    }

    public function test_catalog_covers_each_question_once_and_keeps_passages_whole(): void
    {
        foreach (QudratCategory::all() as $category) {
            $sets = app(PracticeSets::class)->definitions($category);
            $ids = $sets->pluck('question_ids')->flatten();
            $this->assertSame($category->questions()->count(), $ids->count());
            $this->assertSame($ids->count(), $ids->unique()->count());
            if ($category->slug === 'reading_comprehension') {
                foreach ($category->questions()->get()->groupBy('passage_id') as $passage) {
                    $this->assertSame(1, $sets->filter(fn ($set) => count(array_intersect($set['question_ids'], $passage->pluck('id')->all())) > 0)->count());
                }
            } else {
                $this->assertTrue($sets->every(fn ($set) => $set['total'] <= 10));
            }
            $this->actingAs($this->student)->get(route('student.qudrat.category', $category))->assertOk()->assertSee('مجموعات التدريب')->assertDontSee('question_ids');
        }
        $this->assertSame(0, QudratPracticeSession::count());
        $this->get(route('student.qudrat.index'))->assertOk()->assertSee('اختيار مجموعة')->assertDontSee('name="mode"', false);
    }

    public function test_selected_sets_resume_independently_and_retry_uses_same_questions(): void
    {
        $category = QudratCategory::where('slug', 'verbal_analogies')->firstOrFail();
        $sets = app(PracticeSets::class)->definitions($category);
        $practice = app(Practice::class);
        $session = $practice->start($this->student, $category, 'practice', $sets[1]['key']);
        $this->assertSame($sets[1]['question_ids'], $session->items()->pluck('question_id')->all());
        $otherSet = $practice->start($this->student, $category, 'practice', $sets[0]['key']);
        $this->assertNotSame($session->id, $otherSet->id);
        $this->assertSame($session->id, $practice->start($this->student, $category, 'practice', $sets[1]['key'])->id);
        foreach ($session->items()->get() as $item) {
            $practice->visit($this->student, $session, 'answer', $item->id, $item->content_snapshot['correct_key']);
            $practice->visit($this->student, $session, 'next', $item->id);
        }
        $cards = app(Progress::class)->sets($this->student, $category);
        $this->assertSame(100, (int) $cards[1]['latest_completed']['percentage']);
        $this->actingAs($this->student)->get(route('student.qudrat.results', $session))->assertOk()->assertSee($sets[1]['key']);
        $retry = $practice->start($this->student, $category, 'practice', $sets[1]['key']);
        $this->assertSame($session->items()->pluck('question_id')->all(), $retry->items()->pluck('question_id')->all());
        $other = User::create(['name' => 'Other', 'email' => 'other-qudrat-sets@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->assertNull(app(Progress::class)->sets($other, $category)[1]['latest_completed']);
    }

    public function test_archiving_and_sort_edits_do_not_shift_sets_and_foreign_keys_cannot_start(): void
    {
        $category = QudratCategory::where('slug', 'verbal_analogies')->firstOrFail();
        $before = app(PracticeSets::class)->definitions($category);
        QudratQuestion::whereKey($before[0]['question_ids'][0])->update(['archived_at' => now(), 'sort_order' => 999]);
        $after = app(PracticeSets::class)->definitions($category);
        $this->assertSame($before->pluck('question_ids')->all(), $after->pluck('question_ids')->all());
        $this->assertSame(9, $after[0]['available']);
        $other = QudratCategory::where('slug', 'sentence_completion')->firstOrFail();
        $this->actingAs($this->student)->postJson(route('student.qudrat.start', $other), ['mode' => 'practice', 'set_key' => $before[0]['key']])->assertNotFound();
        $this->postJson(route('student.qudrat.start', $category), ['mode' => 'review', 'set_key' => $before[0]['key']])->assertUnprocessable();
        $this->post(route('student.qudrat.start', $category), ['mode' => 'practice', 'set_key' => $before[0]['key']])->assertRedirect();
        $this->assertSame(9, QudratPracticeSession::where('practice_set_key', $before[0]['key'])->firstOrFail()->items()->count());
        $category->update(['is_published' => false]);
        $this->get(route('student.qudrat.category', $category))->assertNotFound();
    }
}
