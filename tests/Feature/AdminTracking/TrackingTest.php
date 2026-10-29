<?php

namespace Tests\Feature\AdminTracking;

use App\Http\Middleware\LogVisit;
use App\Livewire\DeletedStudentsTable;
use App\Livewire\StudentsTable;
use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillQuestion;
use App\Models\QudratCategory;
use App\Models\QudratPassage;
use App\Models\User;
use App\Services\Admin\Tracking\SessionRecords;
use App\Services\Admin\Tracking\StudentTracking;
use App\Services\ArabicSkills\ContentValidator;
use App\Services\ArabicSkills\Practice as ArabicPractice;
use App\Services\Qudrat\Practice as QudratPractice;
use App\Services\Qudrat\QuestionEditor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\AdminTrackingTestCase;

class TrackingTest extends AdminTrackingTestCase
{
    private User $admin;

    private User $student;

    private QudratCategory $category;

    private ArabicSkillBank $bank;

    private StudentTracking $tracking;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'tracking-admin@example.invalid', 'password' => 'local', 'role' => 'admin']);
        $this->student = User::create(['name' => 'Student', 'email' => 'tracking-student@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->category = QudratCategory::create(['slug' => 'verbal_analogies', 'title' => 'التناظر', 'sort_order' => 1]);
        $this->bank = ArabicSkillBank::create(['slug' => 'tracking-test', 'title' => 'المهارات', 'subtitle' => 'اختبار', 'sort_order' => 1, 'source_metadata' => []]);
        $this->tracking = app(StudentTracking::class);
        $this->actingAs($this->admin);
    }

    private function qudratQuestion(int $order = 1, ?QudratCategory $category = null, ?QudratPassage $passage = null)
    {
        return app(QuestionEditor::class)->save(['category_id' => ($category ?? $this->category)->id, 'passage_id' => $passage?->id,
            'sort_order' => $order, 'prompt' => 'السؤال الأصلي '.$order, 'hint' => 'تلميح محفوظ', 'explanation' => 'تفسير محفوظ',
            'correct_option_key' => 'c', 'options' => ['a' => 'أول', 'b' => 'ثان', 'c' => 'ثالث', 'd' => 'رابع']]);
    }

    private function arabicPool(): void
    {
        $source = app(ContentValidator::class)->validateDirectory(base_path('arabic_skills_content'))['banks'][0]['questions'];
        for ($i = 0; $i < 10; $i++) {
            $data = collect($source)->firstWhere('type', ['multiple_choice', 'true_false', 'matching'][$i % 3]);
            ArabicSkillQuestion::create(array_intersect_key($data, array_flip(['skill', 'type', 'prompt', 'options', 'matching_items', 'answer', 'explanation', 'hint', 'scoring', 'max_score'])) + ['bank_id' => $this->bank->id]);
        }
    }

    private function summary(string $program): array
    {
        return $this->tracking->summary($this->student->id)['programs'][$program];
    }

    public function test_selected_set_titles_are_preserved_in_both_program_histories_and_reviews(): void
    {
        $this->qudratQuestion();
        $this->arabicPool();
        $qudratSet = app(\App\Services\Qudrat\PracticeSets::class)->definitions($this->category)->first();
        $arabicSet = app(\App\Services\ArabicSkills\PracticeSets::class)->definitions($this->bank)->first();
        $qudrat = app(QudratPractice::class)->start($this->student, $this->category, 'practice', $qudratSet['key']);
        $arabic = app(ArabicPractice::class)->start($this->student, $this->bank, 'practice', null, $arabicSet['key']);
        $records = app(SessionRecords::class);
        foreach (['qudrat' => $qudrat, 'arabic_skills' => $arabic] as $program => $session) {
            $this->assertSame($session->practice_set_title, $records->history($this->student->id, $program)->first()['practice_set_title']);
            $this->assertSame($session->practice_set_title, $records->review($this->student->id, $program, $session->id)['session']['practice_set_title']);
        }
    }

    public function test_empty_opened_and_draft_sessions_do_not_count_as_participation(): void
    {
        $this->assertNull($this->summary('qudrat')['coverage']);
        $this->assertNull($this->summary('arabic_skills')['first_attempt_accuracy']);
        $this->qudratQuestion();
        $practice = app(QudratPractice::class);
        $session = $practice->start($this->student, $this->category, 'practice');
        $item = $session->items()->first();
        $practice->visit($this->student, $session, 'selection', $item->id, 'c');
        $practice->visit($this->student, $session, 'hint', $item->id);
        $metrics = $this->summary('qudrat');
        $this->assertFalse($metrics['participated']);
        $this->assertSame(0, $metrics['answered_questions']);
        $this->assertSame(0.0, $metrics['coverage']);
        $this->assertNull($metrics['last_practice_at']);
        $this->assertSame(0, $metrics['completed_sessions']);
        $review = app(SessionRecords::class)->review($this->student->id, 'qudrat', $session->id);
        $this->assertSame('unanswered', $review['items'][0]['result']);
        $this->assertNull($review['items'][0]['response']);
        $this->assertNull($review['session']['score']['percentage']);
    }

    public function test_qudrat_retries_repeats_and_unfinished_answers_preserve_first_accuracy(): void
    {
        $this->qudratQuestion();
        $practice = app(QudratPractice::class);
        $session = $practice->start($this->student, $this->category, 'practice');
        $item = $session->items()->first();
        $practice->visit($this->student, $session, 'hint', $item->id);
        $practice->visit($this->student, $session, 'answer', $item->id, 'a');
        $practice->visit($this->student, $session, 'answer', $item->id, 'c');
        $this->assertSame(1, $this->summary('qudrat')['answered_questions']);
        $this->assertSame(0, $this->summary('qudrat')['completed_sessions']);
        $practice->visit($this->student, $session, 'next', $item->id);
        $review = $practice->start($this->student, $this->category, 'review');
        $practice->visit($this->student, $review, 'answer', $review->items()->first()->id, 'c');
        $metrics = $this->summary('qudrat');
        $this->assertSame(100.0, $metrics['coverage']);
        $this->assertSame(0.0, $metrics['first_attempt_accuracy']);
        $this->assertSame(1, $metrics['completed_sessions']);
        $this->assertTrue($metrics['participated']);
        $this->assertNotNull($metrics['last_practice_at']);
        $type = $this->tracking->breakdown($this->student->id, 'qudrat')[0];
        $this->assertSame($metrics, array_intersect_key($type, $metrics));
        $this->assertSame(2, DB::table('qudrat_session_answers')->where('user_id', $this->student->id)->count());
    }

    public function test_first_answer_uses_timestamp_then_id_without_overwriting_history(): void
    {
        $this->qudratQuestion();
        $practice = app(QudratPractice::class);
        $session = $practice->start($this->student, $this->category, 'practice');
        $practice->visit($this->student, $session, 'answer', $session->items()->first()->id, 'a');
        $practice->visit($this->student, $session, 'next', $session->items()->first()->id);
        $review = $practice->start($this->student, $this->category, 'review');
        $practice->visit($this->student, $review, 'answer', $review->items()->first()->id, 'c');
        $answers = DB::table('qudrat_session_answers')->where('user_id', $this->student->id)->orderBy('id')->get();
        // Artificial historical timestamps are confined to this rollback-only fixture.
        DB::table('qudrat_session_answers')->where('id', $answers[0]->id)->update(['submitted_at' => '2026-01-02 00:00:00']);
        DB::table('qudrat_session_answers')->where('id', $answers[1]->id)->update(['submitted_at' => '2026-01-01 00:00:00']);
        $this->assertSame(100.0, $this->summary('qudrat')['first_attempt_accuracy']);
        DB::table('qudrat_session_answers')->where('id', $answers[0]->id)->update(['submitted_at' => '2026-01-01 00:00:00']);
        $this->assertSame(0.0, $this->summary('qudrat')['first_attempt_accuracy']);
    }

    public function test_unavailable_content_leaves_history_and_participation_but_not_coverage(): void
    {
        $question = $this->qudratQuestion();
        $practice = app(QudratPractice::class);
        $session = $practice->start($this->student, $this->category, 'practice');
        $practice->visit($this->student, $session, 'answer', $session->items()->first()->id, 'c');
        $question->update(['archived_at' => now(), 'is_published' => false]);
        $metrics = $this->summary('qudrat');
        $this->assertSame(0, $metrics['available_questions']);
        $this->assertSame(0, $metrics['answered_questions']);
        $this->assertNull($metrics['coverage']);
        $this->assertNull($metrics['first_attempt_accuracy']);
        $this->assertTrue($metrics['participated']);
        $this->assertNotNull($metrics['last_practice_at']);
        $this->category->update(['is_published' => false]);
        $before = DB::table('qudrat_practice_sessions')->where('id', $session->id)->first();
        $records = app(SessionRecords::class);
        $this->assertCount(1, $records->history($this->student->id, 'qudrat')->items());
        $this->assertSame('correct', $records->review($this->student->id, 'qudrat', $session->id)['items'][0]['result']);
        $this->assertEquals($before, DB::table('qudrat_practice_sessions')->where('id', $session->id)->first());
    }

    public function test_arabic_partial_answers_abandonment_and_retries_are_not_completion_or_full_accuracy(): void
    {
        $this->arabicPool();
        $practice = app(ArabicPractice::class);
        $session = $practice->start($this->student, $this->bank);
        foreach ($session->items()->get() as $item) {
            $snapshot = $item->content_snapshot;
            $response = $snapshot['answer'];
            if ($snapshot['type'] === 'matching') {
                $right = $response['matches'][0]['option_id'];
                $response['matches'][0]['option_id'] = collect($snapshot['options'])->pluck('id')->first(fn ($id) => $id !== $right);
            }
            $practice->answer($this->student, $session, $item->id, $response);
            $practice->answer($this->student, $session, $item->id, $snapshot['answer']);
            $practice->next($this->student, $session, $item->id);
        }
        $metrics = $this->summary('arabic_skills');
        $this->assertSame(10, $metrics['answered_questions']);
        $this->assertSame(100.0, $metrics['coverage']);
        $this->assertSame(70.0, $metrics['first_attempt_accuracy']);
        $this->assertSame(1, $metrics['completed_sessions']);
        $review = app(SessionRecords::class)->review($this->student->id, 'arabic_skills', $session->id);
        $this->assertSame(85.0, $review['session']['score']['percentage']);
        $this->assertSame(3, collect($review['items'])->where('result', 'partial')->count());
        $this->assertNull($review['items'][0]['used_hint']);
        $again = $practice->start($this->student, $this->bank);
        $item = $again->items()->first();
        $practice->answer($this->student, $again, $item->id, $item->content_snapshot['answer']);
        $practice->abandon($this->student, $again);
        $this->assertSame($metrics['first_attempt_accuracy'], $this->summary('arabic_skills')['first_attempt_accuracy']);
        $this->assertSame(1, $this->summary('arabic_skills')['completed_sessions']);
        $this->bank->update(['is_active' => false]);
        $this->assertNull($this->summary('arabic_skills')['coverage']);
        $this->assertSame(0, $this->summary('arabic_skills')['answered_questions']);
        $this->assertTrue($this->summary('arabic_skills')['participated']);
        $this->assertSame('abandoned', app(SessionRecords::class)->review($this->student->id, 'arabic_skills', $again->id)['session']['status']);
    }

    public function test_arabic_first_timestamp_ties_and_unknown_history_do_not_use_latest_result(): void
    {
        $this->arabicPool();
        $practice = app(ArabicPractice::class);
        $session = $practice->start($this->student, $this->bank);
        $item = $session->items()->first();
        $practice->answer($this->student, $session, $item->id, $item->content_snapshot['answer']);
        $practice->abandon($this->student, $session);
        $repeat = $practice->start($this->student, $this->bank);
        $same = $repeat->items()->where('question_id', $item->question_id)->first();
        DB::table('arabic_skill_session_questions')->where('id', $item->id)->update(['submitted_at' => '2026-01-02 00:00:00']);
        DB::table('arabic_skill_session_questions')->where('id', $same->id)->update(['submitted_at' => '2026-01-01 00:00:00', 'response' => json_encode($item->content_snapshot['answer']), 'result' => 'incorrect', 'earned_points' => 0]);
        $this->assertSame(0.0, $this->summary('arabic_skills')['first_attempt_accuracy']);
        DB::table('arabic_skill_session_questions')->where('id', $item->id)->update(['submitted_at' => '2026-01-01 00:00:00']);
        $this->assertSame(100.0, $this->summary('arabic_skills')['first_attempt_accuracy']);
        DB::table('arabic_skill_session_questions')->where('id', $item->id)->update(['result' => null]);
        $this->assertNull($this->summary('arabic_skills')['first_attempt_accuracy']);
        $this->assertFalse($this->summary('arabic_skills')['first_answer_results_known']);
        $this->assertSame(1, $this->summary('arabic_skills')['answered_questions']);
    }

    public function test_reviews_use_original_snapshots_passages_and_unanswered_state_without_writes(): void
    {
        $category = QudratCategory::create(['slug' => 'reading_comprehension', 'title' => 'القراءة', 'sort_order' => 2]);
        $passage = QudratPassage::create(['category_id' => $category->id, 'external_key' => 'tracking-passage', 'text' => 'النص الأصلي', 'sort_order' => 1]);
        $question = $this->qudratQuestion(1, $category, $passage);
        $this->qudratQuestion(2, $category, $passage);
        $practice = app(QudratPractice::class);
        $session = $practice->start($this->student, $category, 'practice');
        $practice->visit($this->student, $session, 'answer', $session->items()->first()->id, 'a');
        $passage->update(['text' => 'نص جديد']);
        $question->update(['prompt' => 'سؤال معدل', 'correct_option_key' => 'a']);
        $before = DB::table('qudrat_practice_sessions')->where('id', $session->id)->first();
        $review = app(SessionRecords::class)->review($this->student->id, 'qudrat', $session->id);
        $this->assertSame('النص الأصلي', $review['items'][0]['question']['passage']);
        $this->assertSame('c', $review['items'][0]['question']['correct_key']);
        $this->assertSame('incorrect', $review['items'][0]['result']);
        $this->assertSame('unanswered', $review['items'][1]['result']);
        $this->assertFalse($review['session']['score']['final']);
        $this->assertEquals($before, DB::table('qudrat_practice_sessions')->where('id', $session->id)->first());
        DB::table('qudrat_session_questions')->where('session_id', $session->id)->update(['content_snapshot' => '{}']);
        $broken = app(SessionRecords::class)->review($this->student->id, 'qudrat', $session->id);
        $this->assertFalse($broken['items'][0]['snapshot_available']);
        $this->assertNull($broken['items'][0]['question']);
    }

    public function test_users_endpoints_are_admin_only_student_scoped_and_never_expose_private_user_fields(): void
    {
        $this->qudratQuestion();
        $this->arabicPool();
        $q = app(QudratPractice::class)->start($this->student, $this->category, 'practice');
        $a = app(ArabicPractice::class)->start($this->student, $this->bank);
        $urls = [route('admin.students.tracking.index'), route('admin.students.tracking.show', $this->student->id)];
        foreach (['qudrat' => $q, 'arabic_skills' => $a] as $program => $session) {
            $urls[] = route('admin.students.tracking.program', [$this->student->id, $program]);
            $urls[] = route('admin.students.tracking.review', [$this->student->id, $program, $session->id]);
        }
        foreach ($urls as $url) {
            $this->getJson($url)->assertOk()->assertDontSee('password')->assertDontSee('remember_token');
        }
        $other = User::create(['name' => 'Other', 'email' => 'tracking-other@example.invalid', 'password' => 'local', 'role' => 'student']);
        foreach (['qudrat' => $q, 'arabic_skills' => $a] as $program => $session) {
            $this->getJson(route('admin.students.tracking.review', [$other->id, $program, $session->id]))->assertNotFound();
        }
        $this->getJson(route('admin.students.tracking.show', $this->admin->id))->assertNotFound();
        $this->student->delete();
        $this->getJson(route('admin.students.tracking.show', $this->student->id))->assertOk()->assertJsonPath('blocked', true);
        $this->actingAs($other);
        foreach ($urls as $url) {
            $this->getJson($url)->assertRedirect(route('home'));
        }
        $this->assertFalse(Gate::allows('view-student-tracking'));
        Livewire::test(StudentsTable::class)->assertForbidden();
        Livewire::test(DeletedStudentsTable::class)->assertForbidden();
        $this->app['auth']->forgetGuards();
        foreach ($urls as $url) {
            $this->getJson($url)->assertUnauthorized();
        }
    }

    public function test_directory_filters_pagination_sorting_and_summary_share_metrics_without_n_plus_one(): void
    {
        $this->qudratQuestion();
        $practice = app(QudratPractice::class);
        $session = $practice->start($this->student, $this->category, 'practice');
        $practice->visit($this->student, $session, 'answer', $session->items()->first()->id, 'c');
        for ($i = 0; $i < 5; $i++) {
            User::create(['name' => 'Empty '.$i, 'email' => 'tracking-empty-'.$i.'@example.invalid', 'password' => 'local', 'role' => 'student']);
        }
        $response = $this->getJson(route('admin.students.tracking.index', ['participation' => 'qudrat', 'per_page' => 1]))->assertOk()->assertJsonPath('total', 1);
        $this->assertEquals($this->tracking->summary($this->student->id), $response->json('data.0'));
        foreach (['asc', 'desc'] as $direction) {
            $sorted = $this->getJson(route('admin.students.tracking.index', ['sort' => 'last_practice', 'direction' => $direction]))->assertOk();
            $this->assertSame($this->student->id, $sorted->json('data.0.id'));
        }
        $this->getJson(route('admin.students.tracking.index', ['participation' => 'neither', 'search' => 'tracking-']))->assertJsonPath('total', 5);
        $this->getJson(route('admin.students.tracking.index', ['participation' => 'arabic_skills', 'search' => 'tracking-']))->assertJsonPath('total', 0);
        $this->getJson(route('admin.students.tracking.index', ['search' => $this->student->email]))->assertJsonPath('total', 1);
        $this->getJson(route('admin.students.tracking.index', ['sort' => 'unsafe', 'per_page' => 1000]))->assertUnprocessable();
        DB::enableQueryLog();
        DB::flushQueryLog();
        $this->tracking->directory(['per_page' => 1]);
        $one = count(DB::getQueryLog());
        DB::flushQueryLog();
        $this->tracking->directory(['per_page' => 100]);
        $this->assertSame($one, count(DB::getQueryLog()));
        $this->assertSame(2, $one);
        DB::disableQueryLog();
        $this->getJson(route('admin.students.tracking.index', ['search' => 'tracking-', 'sort' => 'qudrat_coverage']))
            ->assertJsonPath('data.0.id', $this->student->id);
        $ascending = $this->getJson(route('admin.students.tracking.index', ['search' => 'tracking-', 'sort' => 'qudrat_coverage', 'direction' => 'asc']))->assertOk();
        $this->assertSame($this->student->id, collect($ascending->json('data'))->last()['id']);
        $this->student->delete();
        $this->getJson(route('admin.students.tracking.index', ['status' => 'blocked', 'search' => 'tracking-']))->assertJsonPath('total', 1);
        $this->getJson(route('admin.students.tracking.index', ['status' => 'all', 'search' => 'tracking-']))->assertJsonPath('total', 6);
        $this->assertFalse(Gate::allows('view', $session));
        $this->assertTrue(Gate::allows('review', $session));
    }

    public function test_completion_timestamp_counts_but_start_update_and_abandonment_do_not(): void
    {
        $this->qudratQuestion();
        $session = app(QudratPractice::class)->start($this->student, $this->category, 'practice');
        $session->update(['status' => 'completed', 'completed_at' => '2026-01-03 00:00:00']);
        $this->assertSame('2026-01-03 00:00:00', $this->summary('qudrat')['last_practice_at']);
        $this->assertFalse($this->summary('qudrat')['participated']);
        $this->arabicPool();
        $arabic = app(ArabicPractice::class)->start($this->student, $this->bank);
        app(ArabicPractice::class)->abandon($this->student, $arabic);
        $this->assertNull($this->summary('arabic_skills')['last_practice_at']);
        $this->assertSame(0, $this->summary('arabic_skills')['completed_sessions']);
    }

    public function test_programs_users_and_dynamic_types_stay_separate_when_question_ids_overlap(): void
    {
        $this->arabicPool();
        $source = ArabicSkillQuestion::where('bank_id', $this->bank->id)->firstOrFail();
        $id = max((int) DB::table('qudrat_questions')->max('id'), (int) DB::table('arabic_skill_questions')->max('id')) + 1;
        $arabic = new ArabicSkillQuestion;
        $arabic->forceFill(array_intersect_key($source->getAttributes(), array_flip(['skill', 'type', 'prompt', 'explanation', 'hint', 'max_score'])) + ['id' => $id, 'bank_id' => $this->bank->id,
            'options' => $source->options, 'matching_items' => $source->matching_items, 'answer' => $source->answer, 'scoring' => $source->scoring])->save();
        $question = new \App\Models\QudratQuestion;
        $question->forceFill(['id' => $id, 'category_id' => $this->category->id, 'external_key' => 'tracking-overlap', 'prompt' => 'سؤال مستقل',
            'sort_order' => 1, 'correct_option_key' => 'c', 'hint' => 'تلميح', 'explanation' => 'تفسير'])->save();
        foreach (['a', 'b', 'c', 'd'] as $index => $key) {
            $question->options()->create(['local_key' => $key, 'text' => $key, 'sort_order' => $index + 1]);
        }
        $practice = app(QudratPractice::class);
        $session = $practice->start($this->student, $this->category, 'practice');
        $practice->visit($this->student, $session, 'answer', $session->items()->first()->id, 'c');
        $this->assertSame(1, $this->summary('qudrat')['answered_questions']);
        $this->assertSame(0, $this->summary('arabic_skills')['answered_questions']);
        $this->assertSame(11, $this->summary('arabic_skills')['available_questions']);
        $other = User::create(['name' => 'Independent', 'email' => 'independent@example.invalid', 'password' => 'local', 'role' => 'student']);
        $otherSummary = $this->tracking->summary($other->id);
        $this->assertFalse($otherSummary['programs']['qudrat']['participated']);
        $this->assertSame(0, $otherSummary['programs']['qudrat']['answered_questions']);
        $arabic->update(['is_active' => false]);
        $this->assertSame(10, $this->summary('arabic_skills')['available_questions']);
        $this->assertSame($this->summary('arabic_skills'), array_intersect_key($this->tracking->breakdown($this->student->id, 'arabic_skills')[0], $this->summary('arabic_skills')));
    }

    public function test_history_is_paginated_stable_and_filtered_and_arabic_review_does_not_change_snapshots(): void
    {
        $this->arabicPool();
        $practice = app(ArabicPractice::class);
        $session = $practice->start($this->student, $this->bank);
        $item = $session->items()->first();
        $practice->answer($this->student, $session, $item->id, $item->content_snapshot['answer']);
        $before = $item->fresh()->getAttributes();
        ArabicSkillQuestion::whereKey($item->question_id)->firstOrFail()->update(['prompt' => 'تم تعديل المحتوى الحالي']);
        $review = app(SessionRecords::class)->review($this->student->id, 'arabic_skills', $session->id);
        $this->assertSame($item->content_snapshot['prompt'], $review['items'][0]['question']['prompt']);
        $this->assertSame('unanswered', $review['items'][1]['result']);
        $this->assertFalse($review['session']['score']['final']);
        $this->assertSame($before, $item->fresh()->getAttributes());
        for ($i = 0; $i < 21; $i++) {
            DB::table('arabic_skill_sessions')->insert(['user_id' => $this->student->id, 'bank_id' => $this->bank->id,
                'mode' => 'practice', 'status' => 'abandoned', 'current_position' => 1, 'selected_question_count' => 10,
                'available_points' => 20, 'earned_points' => 0, 'started_at' => '2026-01-01 00:00:00']);
        }
        $url = route('admin.students.tracking.program', [$this->student->id, 'arabic_skills']);
        $first = $this->getJson($url.'?type_id='.$this->bank->id)->assertOk()->assertJsonPath('history.total', 22)->assertJsonCount(20, 'history.data');
        $second = $this->getJson($url.'?type_id='.$this->bank->id.'&page=2')->assertOk()->assertJsonCount(2, 'history.data');
        $this->assertEmpty(array_intersect(array_column($first->json('history.data'), 'id'), array_column($second->json('history.data'), 'id')));
        $this->assertStringContainsString('type_id='.$this->bank->id, $first->json('history.next_page_url'));
        $this->getJson($url.'?type_id=99999999')->assertJsonPath('history.total', 0);
        DB::table('arabic_skill_session_questions')->where('id', $item->id)->update(['content_snapshot' => '{}']);
        $this->assertFalse(app(SessionRecords::class)->review($this->student->id, 'arabic_skills', $session->id)['items'][0]['snapshot_available']);
        DB::table('arabic_skill_sessions')->where('id', $session->id)->update(['earned_points' => 21]);
        $invalidScore = app(SessionRecords::class)->review($this->student->id, 'arabic_skills', $session->id)['session']['score'];
        $this->assertFalse($invalidScore['valid']);
        $this->assertNull($invalidScore['percentage']);
    }

    public function test_service_access_is_denied_even_without_route_middleware(): void
    {
        $this->actingAs($this->student);
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->tracking->summary($this->student->id);
    }

    public function test_users_tables_reauthorize_export_requests_after_initial_admin_render(): void
    {
        foreach ([StudentsTable::class, DeletedStudentsTable::class] as $tableClass) {
            $this->actingAs($this->admin);
            $table = Livewire::test($tableClass)->assertOk();
            $this->actingAs($this->student);
            $table->call('exportToXLS')->assertForbidden();
        }
    }
}
