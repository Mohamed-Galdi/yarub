<?php

namespace Tests\Feature\ArabicSkills;

use App\Http\Middleware\LogVisit;
use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillQuestion;
use App\Models\ArabicSkillSession;
use App\Models\User;
use App\Services\ArabicSkills\ContentImporter;
use App\Services\ArabicSkills\ContentValidator;
use App\Services\ArabicSkills\Practice;
use App\Services\ArabicSkills\PracticeSets;
use App\Services\ArabicSkills\Progress;
use Tests\ArabicSkillsTestCase;

class PracticeSetsTest extends ArabicSkillsTestCase
{
    private User $student;
    private ArabicSkillBank $bank;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        $validator = app(ContentValidator::class);
        $content = $validator->withSupplement($validator->validateDirectory(base_path('arabic_skills_content')),
            base_path('arabic_skills_content/supplements/burhan_coffee_filtration.json'));
        app(ContentImporter::class)->import($content);
        $this->student = User::create(['name' => 'Sets student', 'email' => 'sets@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->bank = ArabicSkillBank::where('slug', 'burhan')->firstOrFail();
    }

    public function test_catalog_has_exact_supplement_first_and_original_context_groups_without_writing_sessions(): void
    {
        $sets = app(PracticeSets::class)->definitions($this->bank);
        $this->assertCount(31, $sets);
        $this->assertSame('coffee-filtration', $sets[0]['key']);
        $this->assertSame(8, $sets[0]['available']);
        $this->assertSame(array_map(fn ($i) => sprintf('supp_burhan_coffee_filtration_%02d', $i), range(1, 8)),
            array_map(fn ($id) => ArabicSkillQuestion::findOrFail($id)->source_id, $sets[0]['question_ids']));
        foreach ($sets->slice(1)->values() as $index => $set) {
            $this->assertCount(10, $set['question_ids']);
            $this->assertSame([$index + 1], ArabicSkillQuestion::whereIn('id', $set['question_ids'])->distinct()->pluck('context_group_number')->all());
        }
        $this->actingAs($this->student)->get(route('student.arabic-skills.index'))->assertOk()->assertSee('اختيار مجموعة');
        $this->get(route('student.arabic-skills.bank', $this->bank))->assertOk()
            ->assertSeeInOrder(['وصف تجربة ترشيح القهوة', 'المجموعة 1'])
            ->assertDontSee('النماذج الكاملة')->assertDontSee('سجل التدريب')
            ->assertDontSee('content_snapshot')->assertDontSee('question_ids');
        $this->assertSame(0, ArabicSkillSession::count());
    }

    public function test_eight_question_session_scores_eighteen_points_and_retry_reuses_exact_questions(): void
    {
        $practice = app(Practice::class);
        $session = $practice->start($this->student, $this->bank, 'practice', null, 'coffee-filtration');
        $this->assertSame(8, $session->selected_question_count);
        $this->assertSame(18, $session->available_points);
        $this->assertSame($session->id, $practice->start($this->student, $this->bank, 'practice', null, 'coffee-filtration')->id);
        $otherSet = $practice->start($this->student, $this->bank, 'practice', null, 'context-2');
        $this->assertNotSame($session->id, $otherSet->id);
        foreach ($session->items()->get() as $item) {
            $practice->answer($this->student, $session, $item->id, $item->content_snapshot['answer']);
            $practice->next($this->student, $session, $item->id);
        }
        $this->assertSame(18, $session->fresh()->earned_points);
        $cards = app(Progress::class)->sets($this->student, $this->bank);
        $this->assertSame(100, (int) $cards[0]['latest_completed']['percentage']);
        $other = User::create(['name' => 'Other', 'email' => 'other-sets@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->assertNull(app(Progress::class)->sets($other, $this->bank)[0]['latest_completed']);
        $this->actingAs($this->student)->get(route('student.arabic-skills.results', $session))->assertOk()->assertSee('coffee-filtration');
        $retry = $practice->start($this->student, $this->bank, 'practice', null, 'coffee-filtration');
        $this->assertNotSame($session->id, $retry->id);
        $this->assertSame($session->items()->pluck('question_id')->all(), $retry->items()->pluck('question_id')->all());
    }

    public function test_archiving_keeps_membership_and_invalid_or_foreign_sets_are_rejected(): void
    {
        $before = app(PracticeSets::class)->definitions($this->bank)->firstWhere('key', 'context-1');
        ArabicSkillQuestion::findOrFail($before['question_ids'][0])->update(['is_active' => false]);
        $after = app(PracticeSets::class)->definitions($this->bank)->firstWhere('key', 'context-1');
        $this->assertSame($before['question_ids'], $after['question_ids']);
        $this->assertSame(9, $after['available']);
        $this->actingAs($this->student)->postJson(route('student.arabic-skills.start', $this->bank), ['mode' => 'practice', 'set_key' => 'missing'])->assertNotFound();
        $otherBank = ArabicSkillBank::where('slug', 'nasaq')->firstOrFail();
        $this->postJson(route('student.arabic-skills.start', $otherBank), ['mode' => 'practice', 'set_key' => 'coffee-filtration'])->assertNotFound();
        $this->postJson(route('student.arabic-skills.start', $this->bank), ['mode' => 'model', 'model_id' => $this->bank->models()->firstOrFail()->id, 'set_key' => 'context-1'])->assertUnprocessable();
        $this->post(route('student.arabic-skills.start', $this->bank), ['mode' => 'practice', 'set_key' => 'context-1'])->assertRedirect();
        $session = ArabicSkillSession::where('practice_set_key', 'context-1')->firstOrFail();
        $this->assertSame(9, $session->selected_question_count);
        $this->assertSame(array_slice($before['question_ids'], 1), $session->items()->pluck('question_id')->all());
    }
}
