<?php

namespace Tests\Feature\Qudrat;

use App\Models\QudratCategory;
use App\Models\QudratQuestion;
use App\Models\User;
use App\Services\Qudrat\ContentImporter;
use App\Services\Qudrat\ContentValidator;
use App\Services\Qudrat\ImportEnvironment;
use App\Services\Qudrat\Practice;
use App\Services\Qudrat\ProductionImportApproval;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\QudratTestCase;

class ProductionImportTest extends QudratTestCase
{
    private array $content;

    private string $digest;

    protected function setUp(): void
    {
        parent::setUp();
        $this->assertSame('yarub_qudrat_test_local', DB::connection()->getDatabaseName());
        $validator = app(ContentValidator::class);
        $this->content = $validator->validateDirectory(base_path('qudrat_content'));
        $this->digest = $validator->digest($this->content);
        // Simulate the production environment on the dedicated test DB, never a live connection.
        app()->instance('env', 'production');
        config(['qudrat.allow_production_import' => true]);
    }

    protected function tearDown(): void
    {
        app()->instance('env', 'testing');
        parent::tearDown();
    }

    private function productionOptions(array $changes = []): array
    {
        return array_merge(['--production' => true, '--database' => 'yarub_qudrat_test_local',
            '--sha256' => $this->digest, '--force' => true], $changes);
    }

    public function test_missing_or_incorrect_authorization_refuses_before_database_queries(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        foreach ([[], ['--force' => true], $this->productionOptions(['--database' => 'other_database']),
            $this->productionOptions(['--database' => '']), $this->productionOptions(['--sha256' => '']),
            $this->productionOptions(['--sha256' => str_repeat('0', 64)]),
            $this->productionOptions(['--overwrite-id' => ['verbal_analogies_001']])] as $options) {
            $this->artisan('qudrat:import', $options)->assertExitCode(1);
        }
        config(['qudrat.allow_production_import' => false]);
        $this->artisan('qudrat:import', $this->productionOptions())->assertExitCode(1);
        config(['qudrat.allow_production_import' => true]);
        app()->instance('env', 'testing');
        $this->artisan('qudrat:import', $this->productionOptions())->assertExitCode(1);
        $this->assertSame([], $queries);
        $this->assertSame(0, QudratQuestion::count());
    }

    public function test_dry_run_prints_review_digest_without_production_opt_in_or_queries(): void
    {
        config(['qudrat.allow_production_import' => false]);
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        $this->artisan('qudrat:import', ['--dry-run' => true])
            ->expectsOutput('Content SHA-256: '.$this->digest)->assertExitCode(0);
        $this->artisan('qudrat:import', $this->productionOptions(['--dry-run' => true]))->assertExitCode(1);
        $this->assertSame([], $queries);
    }

    public function test_declined_and_noninteractive_imports_do_not_query_the_database(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        $target = config('database.connections.mysql.host').':'.config('database.connections.mysql.port').'/yarub_qudrat_test_local';
        $this->artisan('qudrat:import', $this->productionOptions(['--force' => false]))
            ->expectsConfirmation('Import reviewed Qudrat content into '.$target.'?', 'no')->assertExitCode(1);
        $this->artisan('qudrat:import', $this->productionOptions(['--force' => false, '--no-interaction' => true]))->assertExitCode(1);
        $this->assertSame([], $queries);
    }

    public function test_explicit_confirmation_imports_published_content(): void
    {
        $target = config('database.connections.mysql.host').':'.config('database.connections.mysql.port').'/yarub_qudrat_test_local';
        $this->artisan('qudrat:import', $this->productionOptions(['--force' => false]))
            ->expectsConfirmation('Import reviewed Qudrat content into '.$target.'?', 'yes')->assertExitCode(0);
        $this->assertSame(6, QudratCategory::where('is_published', true)->count());
        $this->assertSame(1200, QudratQuestion::published()->count());
    }

    public function test_force_reruns_skip_admin_edits_preserve_archives_and_attempt_history_and_do_not_leave_permission_enabled(): void
    {
        $this->artisan('qudrat:import', $this->productionOptions(['--no-interaction' => true]))->assertExitCode(0);
        $student = User::create(['name' => 'Local production test', 'email' => 'production-test@example.invalid', 'password' => 'local', 'role' => 'student']);
        $session = app(Practice::class)->start($student, QudratCategory::orderBy('sort_order')->first(), 'practice')->fresh();
        $item = $session->items()->first();
        app(Practice::class)->visit($student, $session, 'answer', $item->id, 'a');
        $answers = $session->answers()->get()->toArray();
        $snapshots = $session->items()->get()->map->getAttributes()->all();
        $edited = QudratQuestion::where('external_key', 'verbal_analogies_002')->first();
        $optionIds = $edited->options()->pluck('id')->all();
        $edited->update(['prompt' => 'Admin correction']);
        $archived = QudratQuestion::where('external_key', 'verbal_analogies_200')->first();
        $archived->update(['is_published' => false, 'archived_at' => now()]);
        $this->artisan('qudrat:import', $this->productionOptions())->assertExitCode(2);
        $this->assertSame(1200, QudratQuestion::count());
        $this->assertSame('Admin correction', $edited->fresh()->prompt);
        $this->assertSame($optionIds, $edited->options()->pluck('id')->all());
        $this->assertFalse($archived->fresh()->is_published);
        $this->assertNotNull($archived->fresh()->archived_at);
        $this->assertSame($answers, $session->answers()->get()->toArray());
        $this->assertSame($snapshots, $session->items()->get()->map->getAttributes()->all());
        $this->expectException(\RuntimeException::class);
        app(ContentImporter::class)->import($this->content);
    }

    public function test_failure_rolls_back_production_content_transaction(): void
    {
        QudratQuestion::creating(function ($question) {
            if ($question->external_key === 'sentence_completion_010') {
                throw new \RuntimeException('Injected production import failure');
            }
        });
        try {
            $this->artisan('qudrat:import', $this->productionOptions())->assertExitCode(1);
            $this->assertSame(0, QudratCategory::count());
            $this->assertSame(0, QudratQuestion::count());
        } finally {
            Event::forget('eloquent.creating: '.QudratQuestion::class);
        }
    }

    public function test_ambiguous_connection_overrides_and_web_context_are_rejected_without_queries(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        $base = config('database.connections.mysql');
        $approval = new ProductionImportApproval('yarub_qudrat_test_local', $this->digest);
        try {
            foreach ([['url' => 'mysql://unused:unused@127.0.0.1/yarub_qudrat_test_local'],
                ['unix_socket' => '/tmp/unused.sock'], ['read' => ['host' => 'other']], ['write' => ['host' => 'other']]] as $override) {
                config(['database.connections.production_unsafe' => array_merge($base, $override), 'database.default' => 'production_unsafe']);
                DB::purge('production_unsafe');
                try {
                    app(ImportEnvironment::class)->productionTarget($approval, $this->digest);
                    $this->fail('Ambiguous production configuration accepted.');
                } catch (\RuntimeException $exception) {
                    $this->assertStringContainsString('explicitly configured MySQL', $exception->getMessage());
                }
            }
        } finally {
            config(['database.default' => 'mysql']);
        }
        $property = new \ReflectionProperty(app(), 'isRunningInConsole');
        $property->setAccessible(true);
        $property->setValue(app(), false);
        try {
            app(ImportEnvironment::class)->productionTarget($approval, $this->digest);
            $this->fail('Web context accepted production import.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('requires the console', $exception->getMessage());
        } finally {
            $property->setValue(app(), true);
        }
        $this->assertSame([], $queries);
    }

    public function test_process_only_opt_in_works_without_changing_cached_config(): void
    {
        config(['qudrat.allow_production_import' => false]);
        $previous = getenv('QUDRAT_ALLOW_PRODUCTION_IMPORT');
        putenv('QUDRAT_ALLOW_PRODUCTION_IMPORT=true');
        try {
            $target = app(ImportEnvironment::class)->productionTarget(new ProductionImportApproval('yarub_qudrat_test_local', $this->digest), $this->digest);
            $this->assertSame('yarub_qudrat_test_local', $target['database']);
            $this->assertFalse(config('qudrat.allow_production_import'));
        } finally {
            putenv($previous === false ? 'QUDRAT_ALLOW_PRODUCTION_IMPORT' : 'QUDRAT_ALLOW_PRODUCTION_IMPORT='.$previous);
        }
    }

    public function test_actual_selected_schema_is_verified_before_content_writes(): void
    {
        $approval = new ProductionImportApproval('yarub_qudrat_test_local', $this->digest);
        // Both schemas are on the dedicated local MySQL instance; this issues no writes.
        DB::statement('USE yarub_qudrat_dev_local');
        try {
            app(ImportEnvironment::class)->assertSafe($approval, $this->digest);
            $this->fail('Selected schema mismatch accepted.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('connected database differs', $exception->getMessage());
        } finally {
            DB::statement('USE yarub_qudrat_test_local');
        }
        $this->assertSame(0, QudratCategory::count());
    }
}
