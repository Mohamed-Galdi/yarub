<?php

namespace Tests\Feature\ArabicSkills;

use App\Models\ArabicSkillQuestion;
use App\Services\ArabicSkills\ContentValidator;
use App\Services\ArabicSkills\ImportEnvironment;
use App\Services\ArabicSkills\ProductionImportApproval;
use Illuminate\Support\Facades\DB;
use Tests\ArabicSkillsTestCase;

class ProductionImportTest extends ArabicSkillsTestCase
{
    private string $digest;

    protected function setUp(): void
    {
        parent::setUp();
        $validator = app(ContentValidator::class);
        $this->digest = $validator->digest($validator->validateDirectory(base_path('arabic_skills_content')));
        // All production-mode scenarios run on the disposable local test connection.
        app()->instance('env', 'production');
        config(['arabic_skills.allow_production_import' => true]);
    }

    protected function tearDown(): void
    {
        app()->instance('env', 'testing');
        parent::tearDown();
    }

    private function productionOptions(array $changes = []): array
    {
        return array_merge(['--production' => true, '--database' => 'yarub_qudrat_test_local', '--sha256' => $this->digest, '--force' => true], $changes);
    }

    public function test_bad_authorization_and_flag_combinations_fail_without_queries(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        foreach ([[], ['--force' => true], ['--sha256' => $this->digest], ['--database' => 'yarub'],
            $this->productionOptions(['--database' => 'yarub']), $this->productionOptions(['--database' => '']),
            $this->productionOptions(['--sha256' => str_repeat('0', 64)]),
            $this->productionOptions(['--overwrite-id' => ['nasaq_001']]),
            $this->productionOptions(['--dry-run' => true])] as $options) {
            $this->artisan('arabic-skills:import', $options)->assertExitCode(1);
        }
        config(['arabic_skills.allow_production_import' => false]);
        $this->artisan('arabic-skills:import', $this->productionOptions())->assertExitCode(1);
        config(['arabic_skills.allow_production_import' => true]);
        app()->instance('env', 'testing');
        $this->artisan('arabic-skills:import', $this->productionOptions())->assertExitCode(1);
        $this->assertSame([], $queries);
    }

    public function test_dry_run_has_no_database_queries_and_prints_digest(): void
    {
        config(['arabic_skills.allow_production_import' => false]);
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        $this->artisan('arabic-skills:import', ['--dry-run' => true])->expectsOutput('Content SHA-256: '.$this->digest)->assertExitCode(0);
        $this->assertSame([], $queries);
    }

    public function test_confirmation_and_force_modes_preserve_conflicts_and_archive_states(): void
    {
        $target = config('database.connections.mysql.host').':'.config('database.connections.mysql.port').'/yarub_qudrat_test_local';
        $this->artisan('arabic-skills:import', $this->productionOptions(['--force' => false]))
            ->expectsConfirmation('Import reviewed Arabic Skills content into '.$target.'?', 'no')->assertExitCode(1);
        $this->assertSame(0, ArabicSkillQuestion::count());
        $this->artisan('arabic-skills:import', $this->productionOptions(['--force' => false, '--no-interaction' => true]))->assertExitCode(1);
        $this->artisan('arabic-skills:import', $this->productionOptions(['--force' => false]))
            ->expectsConfirmation('Import reviewed Arabic Skills content into '.$target.'?', 'yes')->assertExitCode(0);
        $this->assertSame(1500, ArabicSkillQuestion::active()->count());
        $question = ArabicSkillQuestion::firstOrFail();
        $question->update(['prompt' => 'تعديل المشرف', 'is_active' => false]);
        $this->artisan('arabic-skills:import', $this->productionOptions(['--no-interaction' => true]))->assertExitCode(2);
        $this->assertSame('تعديل المشرف', $question->fresh()->prompt);
        $this->assertFalse($question->fresh()->is_active);
        $this->assertSame(1500, ArabicSkillQuestion::count());
        $this->assertFalse(config('arabic_skills.allow_development_import') && app()->environment('testing'));
    }

    public function test_ambiguous_targets_and_nonconsole_context_fail_without_queries(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        $base = config('database.connections.mysql');
        $approval = new ProductionImportApproval('yarub_qudrat_test_local', $this->digest);
        try {
            foreach ([['url' => 'mysql://unused:unused@127.0.0.1/yarub_qudrat_test_local'], ['unix_socket' => '/tmp/no'], ['read' => ['host' => 'other']], ['write' => ['host' => 'other']]] as $override) {
                config(['database.connections.arabic_unsafe' => array_merge($base, $override), 'database.default' => 'arabic_unsafe']);
                DB::purge('arabic_unsafe');
                try {
                    app(ImportEnvironment::class)->productionTarget($approval, $this->digest);
                    $this->fail('Ambiguous target accepted');
                } catch (\RuntimeException $e) {
                    $this->assertStringContainsString('explicitly configured MySQL', $e->getMessage());
                }
            }
        } finally {
            config(['database.default' => 'mysql']);
        }
        $property = new \ReflectionProperty(app(), 'isRunningInConsole');
        $property->setValue(app(), false);
        try {
            app(ImportEnvironment::class)->productionTarget($approval, $this->digest);
            $this->fail('Web context accepted');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('requires the console', $e->getMessage());
        } finally {
            $property->setValue(app(), true);
        }
        $this->assertSame([], $queries);
    }

    public function test_actual_schema_mismatch_is_rejected_and_process_opt_in_is_request_scoped(): void
    {
        $approval = new ProductionImportApproval('yarub_qudrat_test_local', $this->digest);
        config(['arabic_skills.allow_production_import' => false]);
        $previous = getenv('ARABIC_SKILLS_ALLOW_PRODUCTION_IMPORT');
        putenv('ARABIC_SKILLS_ALLOW_PRODUCTION_IMPORT=true');
        try {
            $this->assertSame('yarub_qudrat_test_local', app(ImportEnvironment::class)->productionTarget($approval, $this->digest)['database']);
            $this->assertFalse(config('arabic_skills.allow_production_import'));
        } finally {
            putenv($previous === false ? 'ARABIC_SKILLS_ALLOW_PRODUCTION_IMPORT' : 'ARABIC_SKILLS_ALLOW_PRODUCTION_IMPORT='.$previous);
        }
        config(['arabic_skills.allow_production_import' => true]);
        DB::statement('USE yarub_qudrat_dev_local');
        try {
            app(ImportEnvironment::class)->assertSafe($approval, $this->digest);
            $this->fail('Selected schema mismatch accepted');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('connected database differs', $e->getMessage());
        } finally {
            DB::statement('USE yarub_qudrat_test_local');
        }
    }

    public function test_existing_review_database_exception_is_local_only(): void
    {
        app()->instance('env', 'testing');
        $base = config('database.connections.mysql');
        config(['database.connections.arabic_review' => array_merge($base, ['host' => '127.0.0.1', 'port' => 3306, 'database' => 'yarub']), 'database.default' => 'arabic_review', 'arabic_skills.existing_local_database' => 'yarub']);
        try {
            try {
                app(ImportEnvironment::class)->assertSafe();
                $this->fail('Review database accepted in tests');
            } catch (\RuntimeException $e) {
                $this->assertStringContainsString('loopback', $e->getMessage());
            }
            app()->instance('env', 'local');
            app(ImportEnvironment::class)->assertSafe();
            $this->assertTrue(true);
        } finally {
            config(['database.default' => 'mysql']);
        }
    }
}
