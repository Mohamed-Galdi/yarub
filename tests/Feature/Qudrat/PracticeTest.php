<?php

namespace Tests\Feature\Qudrat;

use App\Http\Middleware\LogVisit;
use App\Models\QudratCategory;
use App\Models\QudratPassage;
use App\Models\QudratPracticeSession;
use App\Models\QudratSessionAnswer;
use App\Models\User;
use App\Services\Qudrat\Practice;
use App\Services\Qudrat\Progress;
use App\Services\Qudrat\QuestionEditor;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Tests\QudratTestCase;

class PracticeTest extends QudratTestCase
{
    private User $student;

    private QudratCategory $category;

    private Practice $practice;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        $this->student = User::create(['name' => 'Student', 'email' => 'practice@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->category = QudratCategory::create(['slug' => 'verbal_analogies', 'title' => 'التناظر اللفظي', 'sort_order' => 1]);
        $this->practice = app(Practice::class);
    }

    private function question(int $order = 1, ?QudratCategory $category = null, ?QudratPassage $passage = null)
    {
        return app(QuestionEditor::class)->save(['category_id' => ($category ?? $this->category)->id,
            'passage_id' => $passage?->id, 'sort_order' => $order, 'prompt' => 'سؤال '.$order,
            'hint' => 'PRIVATE_HINT_'.$order, 'explanation' => 'PRIVATE_EXPLANATION_'.$order,
            'correct_option_key' => 'c', 'options' => ['a' => 'أول', 'b' => 'ثان', 'c' => 'ثالث', 'd' => 'رابع']]);
    }

    private function start(string $mode = 'practice', ?QudratCategory $category = null): QudratPracticeSession
    {
        return $this->practice->start($this->student, $category ?? $this->category, $mode)->fresh();
    }

    private function submit(QudratPracticeSession $session, string $key = 'c'): array
    {
        $item = $session->items()->where('position', $session->fresh()->current_position)->firstOrFail();

        return $this->practice->visit($this->student, $session, 'answer', $item->id, $key);
    }

    private function finish(QudratPracticeSession $session, string $key = 'c'): void
    {
        while ($session->fresh()->status === 'active') {
            $data = $this->submit($session, $key);
            $this->practice->visit($this->student, $session, 'next', $data['itemId']);
        }
    }

    public function test_all_students_have_access_but_guests_and_admins_do_not(): void
    {
        $this->get(route('student.qudrat.index'))->assertRedirect(route('login'));
        $admin = User::create(['name' => 'Admin', 'email' => 'practice-admin@example.invalid', 'password' => 'local', 'role' => 'admin']);
        $this->actingAs($admin)->get(route('student.qudrat.index'))->assertRedirect(route('home'));
        $this->actingAs($this->student)->get(route('student.qudrat.index'))->assertOk()->assertSee('تدرّب على القدرات اللفظية');
        $this->assertSame(0, QudratPracticeSession::count());
    }

    public function test_start_is_idempotent_and_gets_do_not_create_sessions(): void
    {
        for ($i = 1; $i <= 12; $i++) {
            $this->question($i);
        }
        $session = $this->start();
        $this->assertSame($session->id, $this->start()->id);
        $this->assertSame(10, $session->items()->count());
        $ids = $session->items()->pluck('question_id')->all();
        $this->actingAs($this->student)->get(route('student.qudrat.show', $session))->assertOk();
        $this->assertSame($ids, $session->items()->pluck('question_id')->all());
        $this->assertSame(1, QudratPracticeSession::count());
    }

    public function test_answers_and_hints_never_leak_before_request_or_submission(): void
    {
        $this->question();
        $session = $this->start();
        $data = $this->practice->visit($this->student, $session);
        $this->assertNull($data['hint']);
        $this->assertNull($data['feedback']);
        $this->assertArrayNotHasKey('correct_key', $data['question']);
        $this->actingAs($this->student)->get(route('student.qudrat.show', $session))->assertOk()
            ->assertDontSee('PRIVATE_HINT_1')->assertDontSee('PRIVATE_EXPLANATION_1')->assertDontSee('correct_key')->assertDontSee('content_snapshot');
        $this->post(route('student.qudrat.hint', [$session, $data['itemId']]))->assertRedirect();
        $this->get(route('student.qudrat.show', $session))->assertSee('PRIVATE_HINT_1')->assertDontSee('PRIVATE_EXPLANATION_1');
        $this->post(route('student.qudrat.answer', [$session, $data['itemId']]), ['option' => 'c', 'is_correct' => false])->assertRedirect();
        $this->get(route('student.qudrat.show', $session))->assertSee('PRIVATE_EXPLANATION_1')->assertSee('إجابة صحيحة');
        $this->assertTrue($session->answers()->first()->is_correct);
        $this->assertTrue($session->answers()->first()->used_hint);
    }

    public function test_selections_and_hints_persist_and_answers_grade_once(): void
    {
        $this->question();
        $session = $this->start();
        $item = $session->items()->first();
        $this->practice->visit($this->student, $session, 'selection', $item->id, 'b');
        $this->assertSame('b', $this->practice->visit($this->student, $session)['selected']);
        $this->assertSame(0, QudratSessionAnswer::count());
        $this->practice->visit($this->student, $session, 'hint', $item->id);
        $firstHint = $item->fresh()->hint_requested_at;
        $this->travel(2)->minutes();
        $this->practice->visit($this->student, $session, 'hint', $item->id);
        $this->assertTrue($firstHint->equalTo($item->fresh()->hint_requested_at));
        $this->submit($session, 'b');
        $again = $this->submit($session, 'c');
        $this->assertSame('b', $again['selected']);
        $this->assertFalse($again['feedback']['correct']);
        $this->assertSame(1, QudratSessionAnswer::count());
        $this->assertSame(1, $session->fresh()->current_position);
    }

    public function test_invalid_choices_and_out_of_order_actions_are_rejected(): void
    {
        $this->question(1);
        $this->question(2);
        $session = $this->start();
        $items = $session->items()->get();
        $this->actingAs($this->student);
        $this->post(route('student.qudrat.answer', [$session, $items[0]->id]), ['option' => 'z'])->assertSessionHasErrors('option');
        $this->post(route('student.qudrat.answer', [$session, $items[1]->id]), ['option' => 'a'])->assertStatus(409);
        $this->post(route('student.qudrat.next', [$session, $items[0]->id]))->assertSessionHasErrors('practice');
        $this->assertSame(0, QudratSessionAnswer::count());
        $this->assertSame(1, $session->fresh()->current_position);
        $this->submit($session);
        $this->post(route('student.qudrat.next', [$session, $items[0]->id]))->assertRedirect();
        $this->post(route('student.qudrat.next', [$session, $items[0]->id]))->assertStatus(409);
        $this->assertSame(2, $session->fresh()->current_position);
    }

    public function test_foreign_sessions_and_items_are_never_accessible(): void
    {
        $this->question();
        $session = $this->start();
        $item = $session->items()->first();
        $other = User::create(['name' => 'Other', 'email' => 'other-practice@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->actingAs($other);
        foreach (['show', 'results'] as $route) {
            $this->get(route('student.qudrat.'.$route, $session))->assertForbidden();
        }
        foreach (['answer', 'hint', 'next'] as $route) {
            $this->post(route('student.qudrat.'.$route, [$session, $item->id]), ['option' => 'c'])->assertForbidden();
        }
        $this->patchJson(route('student.qudrat.selection', [$session, $item->id]), ['option' => 'c'])->assertForbidden();
        $this->assertSame(0, QudratSessionAnswer::count());
        $this->assertNull($item->fresh()->hint_requested_at);
    }

    public function test_reading_groups_stay_whole_including_partially_seen_passages(): void
    {
        $category = QudratCategory::create(['slug' => 'reading_comprehension', 'title' => 'استيعاب المقروء', 'sort_order' => 5]);
        foreach ([1, 2, 3] as $n) {
            $passage = QudratPassage::create(['external_key' => 'reading_'.$n, 'category_id' => $category->id, 'text' => 'نص '.$n, 'sort_order' => $n]);
            for ($i = 1; $i <= 7; $i++) {
                $this->question(($n - 1) * 7 + $i, $category, $passage);
            }
        }
        $session = $this->start('practice', $category);
        $this->assertSame(14, $session->items()->count());
        $this->submit($session);
        $session->update(['status' => 'closed', 'closed_at' => now()]);
        $next = $this->start('practice', $category);
        $this->assertSame(14, $next->items()->count());
        $this->assertSame($session->items()->pluck('question_id')->all(), $next->items()->pluck('question_id')->all());
        $this->assertSame('نص 1', $this->practice->visit($this->student, $next)['question']['passage']);
    }

    public function test_snapshots_survive_admin_edits_and_results_include_mistake_passages(): void
    {
        $question = $this->question();
        $session = $this->start();
        $question->update(['prompt' => 'EDITED_PROMPT', 'hint' => 'EDITED_HINT', 'explanation' => 'EDITED_EXPLANATION', 'correct_option_key' => 'a']);
        $question->options()->where('local_key', 'c')->update(['text' => 'EDITED_OPTION']);
        $this->assertSame('سؤال 1', $this->practice->visit($this->student, $session)['question']['prompt']);
        $this->finish($session, 'c');
        $result = $this->practice->results($this->student, $session->fresh());
        $this->assertSame(1, $result['correct']);
        $this->assertSame('PRIVATE_EXPLANATION_1', $result['reviews'][0]['explanation']);
        $this->actingAs($this->student)->get(route('student.qudrat.results', $session))->assertOk()->assertDontSee('EDITED_');
        $this->get(route('student.qudrat.results', ['session' => $session, 'mistakes' => 1]))->assertSee('لا توجد أخطاء');
    }

    public function test_archiving_closes_active_practice_preserves_answers_and_hides_unsubmitted_content(): void
    {
        $this->question(1);
        $second = $this->question(2);
        $session = $this->start();
        $first = $this->submit($session);
        $this->practice->visit($this->student, $session, 'next', $first['itemId']);
        $second->update(['archived_at' => now(), 'is_published' => false]);
        $this->actingAs($this->student)->get(route('student.qudrat.show', $session))->assertRedirect(route('student.qudrat.results', $session));
        $this->assertSame('closed', $session->fresh()->status);
        $this->assertSame(1, $session->answers()->count());
        $this->get(route('student.qudrat.results', $session))->assertOk()->assertSee('حُفظت إجاباتك السابقة')->assertDontSee('PRIVATE_EXPLANATION_2');
        $this->assertSame('restricted', $session->items()->where('question_id', $second->id)->first()->availability);
    }

    public function test_progress_and_first_attempt_accuracy_ignore_retries_and_hidden_questions(): void
    {
        $first = $this->question(1);
        $second = $this->question(2);
        $session = $this->start();
        $this->finish($session, 'b');
        $review = $this->start('review');
        $this->finish($review, 'c');
        $progress = app(Progress::class)->categories($this->student)[0];
        $this->assertSame(2, $progress['attempted']);
        $this->assertSame(100, $progress['progress']);
        $this->assertSame(0, $progress['accuracy']);
        $second->update(['archived_at' => now()]);
        $progress = app(Progress::class)->categories($this->student)[0];
        $this->assertSame(1, $progress['total']);
        $this->assertSame(1, $progress['attempted']);
        $this->assertSame(0, $progress['accuracy']);
        $this->assertSame(4, QudratSessionAnswer::count());
    }

    public function test_empty_and_exhausted_banks_offer_explicit_review(): void
    {
        $this->actingAs($this->student)->post(route('student.qudrat.start', $this->category), ['mode' => 'practice'])->assertSessionHasErrors('practice');
        $this->assertSame(0, QudratPracticeSession::count());
        $this->question();
        $this->finish($this->start());
        $this->post(route('student.qudrat.start', $this->category), ['mode' => 'practice'])->assertSessionHasErrors('practice');
        $this->get(route('student.qudrat.index'))->assertOk()->assertSee('أكملت جميع الأسئلة')->assertSee('مراجعة الأسئلة السابقة');
        $this->post(route('student.qudrat.start', $this->category), ['mode' => 'review'])->assertRedirect();
        $this->assertSame(2, QudratPracticeSession::count());
    }

    public function test_all_six_types_use_the_same_safe_player(): void
    {
        foreach (['verbal_analogies', 'sentence_completion', 'contextual_errors', 'odd_word_out', 'reading_comprehension', 'association_and_difference'] as $position => $slug) {
            $category = $position === 0 ? $this->category : QudratCategory::create(['slug' => $slug, 'title' => 'قسم '.$position, 'sort_order' => $position + 1]);
            $passage = $slug === 'reading_comprehension' ? QudratPassage::create(['external_key' => 'passage', 'category_id' => $category->id, 'text' => "فقرة أولى\nفقرة ثانية", 'sort_order' => 1]) : null;
            $this->question(1, $category, $passage);
            $session = $this->start('practice', $category);
            $this->actingAs($this->student)->get(route('student.qudrat.show', $session))->assertOk()->assertSee('سؤال 1')->assertDontSee('PRIVATE_');
            $this->finish($session, 'b');
            if ($passage) {
                $this->get(route('student.qudrat.results', ['session' => $session, 'mistakes' => 1]))->assertSee('فقرة أولى')->assertSee('PRIVATE_EXPLANATION_1');
            }
        }
    }

    public function test_real_parallel_starts_and_submissions_create_one_session_and_one_answer(): void
    {
        $question = $this->question();
        $this->assertSame('yarub_qudrat_test_local', DB::connection()->getDatabaseName());
        // Workers need committed fixtures. Cleanup below is scoped to these fixture identities.
        DB::commit();
        $workers = [];
        try {
            $run = function (array $arguments) use (&$workers): Process {
                $process = new Process(array_merge([PHP_BINARY, '-c', php_ini_loaded_file(), base_path('tests/Support/qudrat-concurrent-worker.php')], $arguments), base_path());
                $process->setTimeout(30)->start();
                $workers[] = $process;

                return $process;
            };
            $first = $run(['start', (string) $this->student->id, (string) $this->category->id]);
            $second = $run(['start', (string) $this->student->id, (string) $this->category->id]);
            $first->wait();
            $second->wait();
            $this->assertTrue($first->isSuccessful(), $first->getErrorOutput());
            $this->assertTrue($second->isSuccessful(), $second->getErrorOutput());
            $this->assertSame(trim($first->getOutput()), trim($second->getOutput()));
            $session = QudratPracticeSession::where('user_id', $this->student->id)->sole();
            $item = $session->items()->sole();
            $firstAnswer = $run(['answer', (string) $this->student->id, (string) $session->id, (string) $item->id, 'a']);
            $secondAnswer = $run(['answer', (string) $this->student->id, (string) $session->id, (string) $item->id, 'c']);
            $firstAnswer->wait();
            $secondAnswer->wait();
            $this->assertTrue($firstAnswer->isSuccessful(), $firstAnswer->getErrorOutput());
            $this->assertTrue($secondAnswer->isSuccessful(), $secondAnswer->getErrorOutput());
            $answer = $session->answers()->sole();
            $this->assertSame($answer->selected_option_key === 'c', $answer->is_correct);
            $this->assertSame(1, $session->items()->count());
        } finally {
            foreach ($workers as $worker) {
                if ($worker->isRunning()) {
                    $worker->stop();
                }
            }
            $sessionIds = QudratPracticeSession::where('user_id', $this->student->id)->pluck('id');
            DB::table('qudrat_session_answers')->where('user_id', $this->student->id)->delete();
            DB::table('qudrat_session_questions')->whereIn('session_id', $sessionIds)->delete();
            DB::table('qudrat_practice_sessions')->whereIn('id', $sessionIds)->delete();
            DB::table('qudrat_options')->where('question_id', $question->id)->delete();
            DB::table('qudrat_questions')->where('id', $question->id)->delete();
            DB::table('qudrat_categories')->where('id', $this->category->id)->delete();
            DB::table('users')->where('id', $this->student->id)->delete();
            DB::beginTransaction();
        }
    }
}
