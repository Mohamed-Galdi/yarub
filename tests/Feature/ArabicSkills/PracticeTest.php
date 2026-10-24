<?php

namespace Tests\Feature\ArabicSkills;

use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillModel;
use App\Models\ArabicSkillQuestion;
use App\Models\ArabicSkillSession;
use App\Models\User;
use App\Services\ArabicSkills\Attempts;
use App\Services\ArabicSkills\ContentValidator;
use App\Services\ArabicSkills\Grader;
use App\Services\ArabicSkills\Practice;
use App\Services\ArabicSkills\Progress;
use Illuminate\Validation\ValidationException;
use Tests\ArabicSkillsTestCase;

class PracticeTest extends ArabicSkillsTestCase
{
    private User $student;

    private ArabicSkillBank $bank;

    private Practice $practice;

    private array $source;

    protected function setUp(): void
    {
        parent::setUp();
        $this->student = User::create(['name' => 'Local', 'email' => 'phase2@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->bank = ArabicSkillBank::create(['slug' => 'phase2', 'title' => 'بنك الاختبار', 'subtitle' => 'اختبار', 'sort_order' => 1, 'source_metadata' => []]);
        $this->practice = app(Practice::class);
        $this->source = app(ContentValidator::class)->validateDirectory(base_path('arabic_skills_content'))['banks'][0]['questions'];
    }

    private function question(string $type = 'multiple_choice', ?ArabicSkillModel $model = null, ?int $number = null): ArabicSkillQuestion
    {
        $source = collect($this->source)->firstWhere('type', $type);
        $data = array_intersect_key($source, array_flip(['skill', 'type', 'prompt', 'options', 'matching_items', 'answer', 'explanation', 'hint', 'scoring', 'max_score']));
        if ($model) {
            $data += ['source_id' => $model->source_id.'_'.$number, 'model_id' => $model->id, 'number' => $number, 'context_group_number' => (int) ceil($number / 10)];
        }

        return ArabicSkillQuestion::create($data + ['bank_id' => $this->bank->id]);
    }

    private function pool(int $count = 10): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->question(['multiple_choice', 'true_false', 'matching'][$i % 3]);
        }
    }

    private function model(int $number = 1): ArabicSkillModel
    {
        $model = ArabicSkillModel::create(['bank_id' => $this->bank->id, 'source_id' => 'phase2_model_'.$number, 'number' => $number, 'title' => 'نموذج '.$number, 'sort_order' => $number]);
        for ($i = 1; $i <= 30; $i++) {
            $this->question(['multiple_choice', 'true_false', 'matching'][$i % 3], $model, ($number - 1) * 30 + $i);
        }

        return $model;
    }

    private function finish(ArabicSkillSession $session, bool $partial = false): array
    {
        foreach ($session->items()->get() as $item) {
            $answer = $item->content_snapshot['answer'];
            if ($partial && isset($answer['matches'])) {
                $answer['matches'][1]['option_id'] = $answer['matches'][0]['option_id'];
            }
            $this->practice->answer($this->student, $session, $item->id, $answer);
            $this->practice->next($this->student, $session, $item->id);
        }

        return $this->practice->results($this->student, $session);
    }

    private function invalid(callable $action, int $status = 422): void
    {
        try {
            $action();
            $this->fail('Expected rejected action');
        } catch (ValidationException $e) {
            $this->assertSame(422, $status);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame($status, $e->getStatusCode());
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            $this->assertSame(403, $status);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->assertSame(404, $status);
        }
    }

    public function test_start_snapshots_ten_unique_questions_and_resumes_exact_session(): void
    {
        $this->pool(15);
        $session = $this->practice->start($this->student, $this->bank);
        $this->assertSame(10, $session->selected_question_count);
        $this->assertSame(20, $session->available_points);
        $items = $session->items()->get();
        $this->assertCount(10, $items->pluck('question_id')->unique());
        $this->assertSame(range(1, 10), $items->pluck('position')->all());
        $this->assertSame($session->id, $this->practice->start($this->student, $this->bank)->id);
        $this->assertSame($items->pluck('id')->all(), $session->items()->pluck('id')->all());
        $before = $this->practice->show($this->student, $session);
        $this->assertSame($before, $this->practice->show($this->student, $session));
        $this->assertNull($before['response']);
        $this->assertNull($before['feedback']);
        $this->assertFalse($before['question']['has_hint']);
        foreach (['answer', 'explanation', 'hint', 'scoring', 'content_snapshot', 'source_id'] as $private) {
            $this->assertArrayNotHasKey($private, $before['question']);
        }
        $this->assertSame(0, $session->items()->whereNotNull('submitted_at')->count());
    }

    public function test_incomplete_banks_models_and_invalid_mode_fail_without_sessions(): void
    {
        $this->pool(9);
        $this->invalid(fn () => $this->practice->start($this->student, $this->bank));
        $this->invalid(fn () => $this->practice->start($this->student, $this->bank, 'other'));
        $this->invalid(fn () => $this->practice->start($this->student, $this->bank, 'model'));
        $model = $this->model();
        $model->questions()->firstOrFail()->update(['is_active' => false]);
        $this->invalid(fn () => $this->practice->start($this->student, $this->bank, 'model', $model));
        $this->invalid(fn () => $this->practice->start($this->student, $this->bank, 'practice', $model));
        $this->bank->update(['is_active' => false]);
        $this->invalid(fn () => $this->practice->start($this->student, $this->bank));
        $this->assertSame(0, ArabicSkillSession::count());
    }

    public function test_model_order_and_mode_combinations_are_independent(): void
    {
        $model = $this->model();
        $other = $this->model(2);
        $session = $this->practice->start($this->student, $this->bank, 'model', $model);
        $this->assertSame(30, $session->selected_question_count);
        $this->assertSame(60, $session->available_points);
        $this->assertSame($model->questions()->pluck('id')->all(), $session->items()->pluck('question_id')->all());
        $this->practice->start($this->student, $this->bank, 'model', $other);
        $this->practice->start($this->student, $this->bank);
        $this->assertSame(3, ArabicSkillSession::count());
        $foreign = ArabicSkillBank::create(['slug' => 'other', 'title' => 'آخر', 'subtitle' => 'اختبار', 'sort_order' => 2, 'source_metadata' => []]);
        $this->invalid(fn () => $this->practice->start($this->student, $foreign, 'model', $model), 404);
    }

    public function test_all_question_types_grade_from_snapshots_and_feedback_waits_for_complete_submission(): void
    {
        $model = $this->model();
        $session = $this->practice->start($this->student, $this->bank, 'model', $model);
        $first = $session->items()->firstOrFail();
        $this->assertSame('true_false', $first->content_snapshot['type']);
        $this->invalid(fn () => $this->practice->answer($this->student, $session, $first->id, ['option_id' => true]));
        $this->assertNull($first->fresh()->submitted_at);
        $result = $this->finish($session, true);
        $this->assertSame('completed', $result['session']['status']);
        $this->assertSame(50, $result['session']['earned_points']);
        $this->assertSame(20, $result['correct']);
        $this->assertSame(10, $result['partial']);
        $this->assertSame(0, $result['incorrect']);
        $this->assertEqualsWithDelta(50 / 60 * 100, $result['session']['percentage'], 0.00001);
        $this->assertCount(30, $result['reviews']);
        $this->assertSame($first->content_snapshot['explanation'], $result['reviews'][0]['feedback']['explanation']);
        $this->assertSame(50, (int) $session->items()->sum('earned_points'));
    }

    public function test_matching_allows_repeated_options_but_rejects_incomplete_duplicate_unknown_and_extra_fields(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->question('matching');
        }
        $session = $this->practice->start($this->student, $this->bank);
        $item = $session->items()->firstOrFail();
        $right = $item->content_snapshot['answer'];
        foreach ([[], ['matches' => []], ['matches' => [$right['matches'][0]]],
            ['matches' => [$right['matches'][0], $right['matches'][0]]],
            ['matches' => [['item_id' => 'item_1', 'option_id' => 'z'], $right['matches'][1]]],
            ['matches' => [['item_id' => 'unknown', 'option_id' => 'a'], $right['matches'][1]]],
            $right + ['earned_points' => 2], ['matches' => ['item_1' => 'a', 'item_2' => 'b']]] as $bad) {
            $this->invalid(fn () => $this->practice->answer($this->student, $session, $item->id, $bad));
            $this->assertNull($item->fresh()->submitted_at);
            $this->assertSame(0, $session->fresh()->earned_points);
            $this->assertNull($this->practice->show($this->student, $session)['feedback']);
        }
        $right['matches'][1]['option_id'] = $right['matches'][0]['option_id'];
        $feedback = $this->practice->answer($this->student, $session, $item->id, $right)['feedback'];
        $this->assertSame(1, $feedback['earned_points']);
        $this->assertSame('partial', $feedback['result']);
        $this->assertCount(2, $feedback['answer']['matches']);
        $this->practice->next($this->student, $session, $item->id);
        $item = $session->items()->where('position', 2)->firstOrFail();
        $wrong = $item->content_snapshot['answer'];
        foreach ($wrong['matches'] as &$match) {
            $match['option_id'] = $match['option_id'] === 'a' ? 'b' : 'a';
        }
        $this->assertSame(0, $this->practice->answer($this->student, $session, $item->id, $wrong)['feedback']['earned_points']);
    }

    public function test_retries_stale_tabs_and_incomplete_sessions_never_inflate_points_or_complete(): void
    {
        $this->pool();
        $session = $this->practice->start($this->student, $this->bank);
        $items = $session->items()->get();
        $first = $items[0];
        $this->invalid(fn () => $this->practice->next($this->student, $session, $first->id));
        $this->invalid(fn () => $this->practice->results($this->student, $session), 409);
        $this->invalid(fn () => $this->practice->answer($this->student, $session, $items[1]->id, $items[1]->content_snapshot['answer']), 409);
        $this->practice->answer($this->student, $session, $first->id, $first->content_snapshot['answer']);
        $this->practice->answer($this->student, $session, $first->id, ['invalid' => 'retry']);
        $this->assertSame(2, $session->fresh()->earned_points);
        $this->assertSame($first->content_snapshot['answer'], $first->fresh()->response);
        $this->practice->next($this->student, $session, $first->id);
        $this->practice->next($this->student, $session, $first->id);
        $this->practice->answer($this->student, $session, $first->id, []);
        $this->assertSame(2, $session->fresh()->current_position);
        $this->assertSame(1, $session->items()->whereNotNull('submitted_at')->count());
        $this->practice->abandon($this->student, $session);
        $this->invalid(fn () => $this->practice->answer($this->student, $session, $items[1]->id, $items[1]->content_snapshot['answer']), 409);
        $this->invalid(fn () => $this->practice->results($this->student, $session), 409);
        $this->assertNotSame($session->id, $this->practice->start($this->student, $this->bank)->id);
    }

    public function test_edits_and_deactivation_do_not_change_active_or_finished_attempts(): void
    {
        $this->pool();
        $session = $this->practice->start($this->student, $this->bank);
        $item = $session->items()->firstOrFail();
        $before = $this->practice->show($this->student, $session);
        $question = $item->question()->firstOrFail();
        $changedAnswer = $question->answer;
        if ($question->type === 'matching') {
            foreach ($changedAnswer['matches'] as &$match) {
                $match['option_id'] = $match['option_id'] === 'a' ? 'b' : 'a';
            }
        } else {
            $changedAnswer['option_id'] = collect($question->options)->first(fn ($o) => $o['id'] !== $changedAnswer['option_id'])['id'];
        }
        $question->update(['prompt' => 'تغيير النص', 'explanation' => 'تغيير التفسير', 'answer' => $changedAnswer, 'is_active' => false]);
        $this->bank->update(['is_active' => false]);
        $this->assertSame($session->id, $this->practice->start($this->student, $this->bank)->id);
        $this->assertSame($before, $this->practice->show($this->student, $session));
        $result = $this->finish($session);
        $this->assertSame(20, $result['session']['earned_points']);
        $this->assertSame($before['question']['prompt'], $result['reviews'][0]['question']['prompt']);
        $this->assertSame($item->content_snapshot['explanation'], $result['reviews'][0]['feedback']['explanation']);
        $this->invalid(fn () => $this->practice->answer($this->student, $session, $item->id, $item->content_snapshot['answer']), 409);
        $this->invalid(fn () => $this->practice->start($this->student, $this->bank));
    }

    public function test_ownership_is_checked_from_database_on_every_operation_and_hint_has_no_answer_leak(): void
    {
        $this->pool();
        $session = $this->practice->start($this->student, $this->bank);
        $item = $session->items()->firstOrFail();
        $this->invalid(fn () => $this->practice->hint($this->student, $session, $item->id));
        $other = User::create(['name' => 'Other', 'email' => 'other-phase2@example.invalid', 'password' => 'local', 'role' => 'student']);
        foreach ([fn () => $this->practice->show($other, $session), fn () => $this->practice->answer($other, $session, $item->id, []),
            fn () => $this->practice->next($other, $session, $item->id), fn () => $this->practice->hint($other, $session, $item->id),
            fn () => $this->practice->abandon($other, $session), fn () => $this->practice->results($other, $session)] as $action) {
            $this->invalid($action, 403);
        }
        $forged = clone $session;
        $forged->user_id = $other->id;
        $this->invalid(fn () => $this->practice->show($other, $forged), 403);
        $admin = new User(['role' => 'admin']);
        $this->invalid(fn () => $this->practice->start($admin, $this->bank), 403);
        $this->practice->abandon($this->student, $session);
        $item->question()->firstOrFail()->update(['hint' => 'تلميح حقيقي']);
        // Force a model-size pool of identical genuine hints for deterministic random selection.
        foreach ($this->bank->questions()->get() as $q) {
            $q->update(['hint' => 'تلميح حقيقي']);
        }
        $session = $this->practice->start($this->student, $this->bank);
        $item = $session->items()->firstOrFail();
        $this->assertSame(['item_id' => $item->id, 'hint' => 'تلميح حقيقي'], $this->practice->hint($this->student, $session, $item->id));
        $this->assertTrue($this->practice->show($this->student, $session)['question']['has_hint']);
        $this->assertNull($this->practice->show($this->student, $session)['feedback']);
    }

    public function test_latest_result_priority_uses_timestamp_then_id_and_ignores_unsubmitted_views(): void
    {
        $this->pool(12);
        $questions = $this->bank->questions()->orderBy('id')->get();
        $history = ArabicSkillSession::create(['user_id' => $this->student->id, 'bank_id' => $this->bank->id, 'mode' => 'model', 'status' => 'abandoned', 'selected_question_count' => 12, 'available_points' => 24, 'started_at' => now()]);
        foreach ($questions->take(10) as $index => $q) {
            $history->items()->create(['bank_id' => $this->bank->id, 'question_id' => $q->id, 'position' => $index + 1, 'content_snapshot' => [], 'response' => [], 'earned_points' => 0, 'result' => 'incorrect', 'submitted_at' => now()->subDay()]);
        }
        // The first question's newer correct result supersedes its older miss.
        $newer = ArabicSkillSession::create(['user_id' => $this->student->id, 'bank_id' => $this->bank->id, 'mode' => 'practice', 'status' => 'abandoned', 'selected_question_count' => 10, 'available_points' => 20, 'started_at' => now()]);
        $newer->items()->create(['bank_id' => $this->bank->id, 'question_id' => $questions[0]->id, 'position' => 1, 'content_snapshot' => [], 'response' => [], 'earned_points' => 2, 'result' => 'correct', 'submitted_at' => now()]);
        $newer->items()->create(['bank_id' => $this->bank->id, 'question_id' => $questions[10]->id, 'position' => 2, 'content_snapshot' => []]);
        $latest = app(Attempts::class)->latest($this->student, $this->bank->id);
        $this->assertSame('correct', $latest[$questions[0]->id]->result);
        $session = $this->practice->start($this->student, $this->bank);
        $ids = $session->items()->pluck('question_id')->all();
        $this->assertContains($questions[10]->id, array_slice($ids, 0, 2));
        $this->assertContains($questions[11]->id, array_slice($ids, 0, 2));
        $this->assertNotContains($questions[0]->id, $ids);
        // A greater row ID with an older timestamp cannot supersede the newer correct result.
        $lateInsert = ArabicSkillSession::create(['user_id' => $this->student->id, 'bank_id' => $this->bank->id, 'mode' => 'practice', 'status' => 'abandoned', 'selected_question_count' => 10, 'available_points' => 20, 'started_at' => now()]);
        $lateInsert->items()->create(['bank_id' => $this->bank->id, 'question_id' => $questions[0]->id, 'position' => 1, 'content_snapshot' => [], 'response' => [], 'earned_points' => 0, 'result' => 'incorrect', 'submitted_at' => now()->subDays(2)]);
        $this->assertSame('correct', app(Attempts::class)->latest($this->student, $this->bank->id)[$questions[0]->id]->result);
        $newestTime = $newer->items()->where('position', 1)->firstOrFail()->submitted_at;
        $tie = ArabicSkillSession::create(['user_id' => $this->student->id, 'bank_id' => $this->bank->id, 'mode' => 'practice', 'status' => 'abandoned', 'selected_question_count' => 10, 'available_points' => 20, 'started_at' => now()]);
        $tie->items()->create(['bank_id' => $this->bank->id, 'question_id' => $questions[0]->id, 'position' => 1, 'content_snapshot' => [], 'response' => [], 'earned_points' => 1, 'result' => 'partial', 'submitted_at' => $newestTime]);
        $this->assertSame('partial', app(Attempts::class)->latest($this->student, $this->bank->id)[$questions[0]->id]->result);
    }

    public function test_progress_counts_distinct_active_coverage_and_completed_only_results(): void
    {
        $this->pool();
        $this->assertSame(0, app(Progress::class)->bank($this->student, $this->bank)['attempted']);
        $session = $this->practice->start($this->student, $this->bank);
        $this->finish($session);
        $this->finish($this->practice->start($this->student, $this->bank));
        $progress = app(Progress::class)->bank($this->student, $this->bank);
        $this->assertSame(10, $progress['attempted']);
        $this->assertSame(100, $progress['coverage']);
        $this->assertSame(2, $progress['completed_sessions']);
        $this->bank->questions()->firstOrFail()->update(['is_active' => false]);
        $progress = app(Progress::class)->bank($this->student, $this->bank);
        $this->assertSame(9, $progress['attempted']);
        $this->assertSame(9, $progress['total']);
        $this->assertFalse($progress['can_start_practice']);
        $this->assertSame(2, app(Progress::class)->history($this->student, $this->bank)['total']);
    }

    public function test_grader_scores_correct_and_incorrect_answers_for_every_type(): void
    {
        foreach (['multiple_choice', 'true_false', 'matching'] as $type) {
            $snapshot = collect($this->source)->firstWhere('type', $type);
            $right = $snapshot['answer'];
            $this->assertSame(2, app(Grader::class)->grade($snapshot, $right)['earned_points']);
            $wrong = $right;
            if ($type === 'matching') {
                foreach ($wrong['matches'] as &$match) {
                    $match['option_id'] = $match['option_id'] === 'a' ? 'b' : 'a';
                }
                unset($match);
            } else {
                $wrong['option_id'] = collect($snapshot['options'])->first(fn ($o) => $o['id'] !== $right['option_id'])['id'];
            }
            $this->assertSame('incorrect', app(Grader::class)->grade($snapshot, $wrong)['result']);
            $this->assertSame(0, app(Grader::class)->grade($snapshot, $wrong)['earned_points']);
            $this->invalid(fn () => app(Grader::class)->grade($snapshot, $right + ['score' => 2]));
        }
    }

    public function test_failed_total_update_rolls_back_the_answer_atomically(): void
    {
        $this->pool();
        $session = $this->practice->start($this->student, $this->bank);
        $item = $session->items()->firstOrFail();
        $event = 'eloquent.updating: '.ArabicSkillSession::class;
        \Illuminate\Support\Facades\Event::listen($event, function () {
            throw new \RuntimeException('simulated score persistence failure');
        });
        try {
            $this->practice->answer($this->student, $session, $item->id, $item->content_snapshot['answer']);
            $this->fail('Expected failure');
        } catch (\RuntimeException $e) {
            $this->assertSame('simulated score persistence failure', $e->getMessage());
            $this->assertNull($item->fresh()->submitted_at);
            $this->assertNull($item->fresh()->response);
            $this->assertSame(0, $session->fresh()->earned_points);
        } finally {
            \Illuminate\Support\Facades\Event::forget($event);
        }
    }

    public function test_model_progress_history_privacy_and_in_progress_states(): void
    {
        $model = $this->model();
        $completed = $this->practice->start($this->student, $this->bank, 'model', $model);
        $this->finish($completed);
        $active = $this->practice->start($this->student, $this->bank, 'model', $model);
        $progress = app(Progress::class)->bank($this->student, $this->bank);
        $this->assertSame($completed->id, $progress['models'][0]['latest_completed']['id']);
        $this->assertSame($active->id, $progress['models'][0]['resume']['id']);
        $this->assertSame(1, $progress['completed_sessions']);
        $this->practice->abandon($this->student, $active);
        $history = app(Progress::class)->history($this->student, $this->bank);
        $this->assertSame('abandoned', $history['sessions'][0]['status']);
        $this->assertSame('completed', $history['sessions'][1]['status']);
        $other = User::create(['name' => 'Other', 'email' => 'history-phase2@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->assertSame(0, app(Progress::class)->history($other, $this->bank)['total']);
        $this->assertSame(0, app(Progress::class)->bank($other, $this->bank)['attempted']);
        $this->assertSame(1, count(app(Progress::class)->banks($this->student)));
        $model->questions()->firstOrFail()->update(['is_active' => false]);
        $this->assertFalse(app(Progress::class)->bank($this->student, $this->bank)['models'][0]['can_start']);
    }
}
