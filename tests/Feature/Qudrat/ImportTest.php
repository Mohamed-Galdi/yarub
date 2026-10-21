<?php

namespace Tests\Feature\Qudrat;

use App\Models\QudratCategory;
use App\Models\QudratOption;
use App\Models\QudratPassage;
use App\Models\QudratPracticeSession;
use App\Models\QudratQuestion;
use App\Models\QudratSessionAnswer;
use App\Models\QudratSessionQuestion;
use App\Models\User;
use App\Services\Qudrat\ContentImporter;
use App\Services\Qudrat\ContentValidator;
use App\Services\Qudrat\ImportEnvironment;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Tests\QudratTestCase;

class ImportTest extends QudratTestCase
{
    private function content(): array
    {
        return app(ContentValidator::class)->validateDirectory(base_path('qudrat_content'));
    }

    private function import(?array $content = null, array $overwrite = []): array
    {
        return app(ContentImporter::class)->import($content ?? $this->content(), $overwrite);
    }

    public function test_full_draft_import_and_repeat_preserve_ids_and_timestamps(): void
    {
        $report = $this->import();
        foreach (['categories' => 6, 'passages' => 40, 'questions' => 1200, 'options' => 4800] as $entity => $count) {
            $this->assertSame($count, $report['counts'][$entity]['created']);
            $this->assertSame($count, DB::table('qudrat_'.$entity)->count());
        }
        $this->assertSame(0, QudratCategory::where('is_published', true)->count());
        $this->assertSame(0, QudratQuestion::where('is_published', true)->count());
        $this->assertSame(0, QudratQuestion::published()->count());
        $before = QudratOption::orderBy('id')->get()->toArray();
        $questionTimes = QudratQuestion::pluck('updated_at', 'id')->all();
        $rerun = $this->import();
        foreach (['categories' => 6, 'passages' => 40, 'questions' => 1200, 'options' => 4800] as $entity => $count) {
            $this->assertSame($count, $rerun['counts'][$entity]['unchanged']);
            $this->assertSame(0, $rerun['counts'][$entity]['created']);
        }
        $this->assertSame($before, QudratOption::orderBy('id')->get()->toArray());
        $this->assertEquals($questionTimes, QudratQuestion::pluck('updated_at', 'id')->all());
        $this->assertSame(0, QudratQuestion::where('category_id', QudratCategory::where('slug', 'reading_comprehension')->value('id'))->whereNull('passage_id')->count());
    }

    public function test_source_updates_preserve_option_identity_publication_and_history(): void
    {
        $content = $this->content();
        $this->import($content);
        $question = QudratQuestion::with('options')->first();
        $question->update(['is_published' => true, 'archived_at' => now()]);
        $optionId = $question->options[0]->id;
        $user = User::create(['name' => 'Local student', 'email' => 'qudrat@example.invalid', 'password' => 'not-live', 'role' => 'student']);
        $session = $user->qudratSessions()->create(['category_id' => $question->category_id, 'started_at' => now()]);
        $snapshot = ['prompt' => $question->prompt, 'correct_option_key' => $question->correct_option_key, 'explanation' => $question->explanation];
        $item = $session->items()->create(['question_id' => $question->id, 'position' => 1, 'content_snapshot' => $snapshot, 'draft_option_key' => 'b']);
        $answer = $session->answers()->create(['session_question_id' => $item->id, 'question_id' => $question->id, 'user_id' => $user->id,
            'selected_option_key' => 'a', 'is_correct' => false, 'used_hint' => true, 'submitted_at' => now()]);
        $beforeAnswer = $answer->fresh()->getAttributes();
        $content[0]['questions'][0]['question'] .= '؟';
        $content[0]['questions'][0]['options'][0]['text'] .= ' جديد';
        $report = $this->import($content);
        $this->assertSame(1, $report['counts']['questions']['updated']);
        $this->assertSame(1, $report['counts']['options']['updated']);
        $this->assertSame($optionId, $question->options()->where('local_key', 'a')->value('id'));
        $this->assertTrue($question->fresh()->is_published);
        $this->assertNotNull($question->fresh()->archived_at);
        $this->assertEquals($snapshot, $item->fresh()->content_snapshot);
        $this->assertSame('b', $item->fresh()->draft_option_key);
        $this->assertSame($beforeAnswer, $answer->fresh()->getAttributes());
    }

    public function test_admin_edits_are_conflicts_even_with_unchanged_input_and_overwrite_is_targeted(): void
    {
        $this->import();
        $questions = QudratQuestion::orderBy('id')->limit(2)->get();
        $questions[0]->update(['prompt' => 'تحرير المشرف']);
        $questions[1]->options()->where('local_key', 'a')->update(['text' => 'خيار محرر']);
        $report = $this->import();
        $this->assertSame(2, $report['counts']['questions']['conflicting']);
        $this->assertSame('تحرير المشرف', $questions[0]->fresh()->prompt);
        $report = $this->import(null, [$questions[0]->external_key]);
        $this->assertSame(1, $report['counts']['questions']['conflicting']);
        $this->assertSame('مفتاح : باب', $questions[0]->fresh()->prompt);
        $this->assertSame('خيار محرر', $questions[1]->options()->where('local_key', 'a')->value('text'));
    }

    public function test_shared_passage_conflicts_block_dependents_and_preserve_the_baseline(): void
    {
        $this->import();
        $passage = QudratPassage::first();
        $baseline = $passage->import_checksum;
        $passage->update(['text' => 'نص محرر']);
        $report = $this->import();
        $this->assertSame(1, $report['counts']['passages']['conflicting']);
        $this->assertSame(3, $report['counts']['questions']['skipped']);
        $this->assertSame('نص محرر', $passage->fresh()->text);
        $this->assertSame($baseline, $passage->fresh()->import_checksum);
        $report = $this->import(null, [$passage->external_key]);
        $this->assertSame([], $report['conflicts']);
    }

    public function test_category_metadata_conflict_and_untracked_collision_are_preserved(): void
    {
        $this->import();
        $category = QudratCategory::first();
        $category->update(['title' => 'عنوان المشرف', 'is_published' => true]);
        $question = QudratQuestion::where('category_id', '!=', $category->id)->first();
        $question->update(['import_checksum' => null, 'prompt' => 'سجل يدوي']);
        $report = $this->import();
        $this->assertSame(1, $report['counts']['categories']['conflicting']);
        $this->assertSame(201, $report['counts']['questions']['skipped']);
        $this->assertSame('عنوان المشرف', $category->fresh()->title);
        $report = $this->import(null, [$category->slug, $question->external_key]);
        $this->assertSame([], $report['conflicts']);
        $this->assertTrue($category->fresh()->is_published);
    }

    public function test_invalid_late_input_is_rejected_before_any_write(): void
    {
        $content = $this->content();
        $content[5]['questions'][199]['correct_option_id'] = 'z';
        try {
            $this->import($content);
            $this->fail('Invalid data accepted.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('answer reference', $exception->getMessage());
        }
        $this->assertSame(0, QudratCategory::count());
        $this->assertSame(0, QudratQuestion::count());
    }

    public function test_runtime_failure_rolls_back_all_content(): void
    {
        QudratQuestion::creating(function ($question) {
            if ($question->external_key === 'sentence_completion_010') {
                throw new \RuntimeException('Injected import failure');
            }
        });
        try {
            $this->import();
            $this->fail('Expected injected failure.');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Injected import failure', $exception->getMessage());
        } finally {
            Event::forget('eloquent.creating: '.QudratQuestion::class);
        }
        $this->assertSame(0, QudratCategory::count());
        $this->assertSame(0, QudratOption::count());
    }

    public function test_records_absent_from_source_are_not_deleted(): void
    {
        $this->import();
        $question = QudratQuestion::first()->replicate();
        $question->external_key = 'admin_created_question';
        $question->save();
        $this->import();
        $this->assertSame(1201, QudratQuestion::count());
        $this->assertNotNull($question->fresh());
    }

    public function test_database_rejects_cross_category_passage_and_duplicate_option_identity(): void
    {
        $this->import();
        $question = QudratQuestion::first();
        try {
            $question->update(['passage_id' => QudratPassage::first()->id]);
            $this->fail('Cross-category passage accepted.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('qudrat_question_passage_fk', $exception->getMessage());
        }
        $this->expectException(QueryException::class);
        $question->options()->create(['local_key' => 'a', 'text' => 'duplicate', 'sort_order' => 1]);
    }

    public function test_dry_run_never_queries_the_database_and_conflicts_have_a_distinct_exit_code(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        $this->artisan('qudrat:import', ['--dry-run' => true])->assertExitCode(0);
        $this->assertSame([], $queries);
        $this->assertSame(0, QudratCategory::count());
        $this->artisan('qudrat:import')->assertExitCode(0);
        QudratQuestion::first()->update(['prompt' => 'تحرير']);
        $this->artisan('qudrat:import')->assertExitCode(2);
    }

    public function test_mutating_import_refuses_production_or_missing_opt_in(): void
    {
        config(['qudrat.allow_development_import' => false]);
        $this->artisan('qudrat:import')->assertExitCode(1);
        config(['qudrat.allow_development_import' => true]);
        app()->instance('env', 'production');
        $this->artisan('qudrat:import')->assertExitCode(1);
        $this->assertSame(0, QudratCategory::count());
    }

    public function test_unknown_overwrite_identity_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->import(null, ['typo']);
    }

    public function test_session_owner_identity_and_historical_snapshots_are_protected(): void
    {
        $category = QudratCategory::create(['slug' => 'local_fixture', 'title' => 'محلي', 'sort_order' => 1]);
        $question = $category->questions()->create(['external_key' => 'local_question', 'prompt' => 'سؤال', 'sort_order' => 1, 'correct_option_key' => 'a']);
        $firstUser = User::create(['name' => 'First', 'email' => 'first@example.invalid', 'password' => 'local', 'role' => 'student']);
        $secondUser = User::create(['name' => 'Second', 'email' => 'second@example.invalid', 'password' => 'local', 'role' => 'student']);
        $session = $firstUser->qudratSessions()->create(['category_id' => $category->id, 'started_at' => now()]);
        $item = $session->items()->create(['question_id' => $question->id, 'position' => 1, 'content_snapshot' => ['prompt' => 'سؤال أصلي']]);
        $data = ['session_question_id' => $item->id, 'session_id' => $session->id, 'question_id' => $question->id,
            'user_id' => $secondUser->id, 'selected_option_key' => 'a', 'is_correct' => true, 'submitted_at' => now()];
        try {
            QudratSessionAnswer::create($data);
            $this->fail('An answer was assigned to a different session owner.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('qudrat_answer_owner_fk', $exception->getMessage());
        }
        $data['user_id'] = $firstUser->id;
        $answer = QudratSessionAnswer::create($data);
        try {
            $answer->update(['is_correct' => false]);
            $this->fail('Historical answer changed.');
        } catch (\LogicException $exception) {
            $this->assertTrue($answer->fresh()->is_correct);
        }
        try {
            $item->update(['content_snapshot' => ['prompt' => 'changed']]);
            $this->fail('Historical snapshot changed.');
        } catch (\LogicException $exception) {
            $this->assertSame(['prompt' => 'سؤال أصلي'], $item->fresh()->content_snapshot);
        }
        $this->expectException(QueryException::class);
        $question->delete();
    }

    public function test_remote_database_config_is_refused_before_opening_a_connection(): void
    {
        config(['database.connections.qudrat_unsafe' => array_merge(config('database.connections.mysql'), [
            'host' => '203.0.113.1', 'database' => 'yarub_qudrat_dev_remote',
        ]), 'database.default' => 'qudrat_unsafe']);
        try {
            app(ImportEnvironment::class)->assertSafe();
            $this->fail('Remote connection accepted.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('loopback', $exception->getMessage());
        } finally {
            config(['database.default' => 'mysql']);
        }
    }

    public function test_content_and_session_policies_do_not_grant_student_entitlement(): void
    {
        $student = new User(['role' => 'student']);
        $student->id = 10;
        $other = new User(['role' => 'student']);
        $other->id = 11;
        $admin = new User(['role' => 'admin']);
        $admin->id = 42;
        $question = new QudratQuestion;
        $session = new QudratPracticeSession(['user_id' => 10, 'status' => 'active']);
        $this->assertTrue(Gate::forUser($admin)->allows('update', $question));
        $this->assertFalse(Gate::forUser($student)->allows('view', $question));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $question));
        $this->assertTrue(Gate::forUser($student)->allows('view', $session));
        $this->assertFalse(Gate::forUser($other)->allows('view', $session));
        $this->assertFalse(Gate::forUser($admin)->allows('view', $session));
        $this->assertFalse(Gate::forUser($student)->allows('create', QudratPracticeSession::class));
        $this->assertArrayNotHasKey('correct_option_key', (new QudratQuestion(['correct_option_key' => 'a']))->toArray());
        $this->assertArrayNotHasKey('content_snapshot', (new QudratSessionQuestion(['content_snapshot' => ['answer' => 'a']]))->toArray());
    }
}
