<?php

namespace Tests;

use App\Services\Qudrat\ImportEnvironment;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

abstract class QudratTestCase extends TestCase
{
    public function createApplication(): \Illuminate\Foundation\Application
    {
        $runtime = dirname(__DIR__).'/storage/app/qudrat-local';
        if (! is_file($runtime.'/.env')) {
            throw new \RuntimeException('Run tools/qudrat-local.ps1 start before Qudrat database tests.');
        }
        // Keep tests on their dedicated connection even when the review server uses the owner's local DB.
        $values = \Dotenv\Dotenv::createArrayBacked($runtime, '.env.testing')->load();
        $values = array_merge($values, [
            'APP_ENV' => 'testing', 'DB_DATABASE' => 'yarub_qudrat_test_local', 'QUDRAT_EXISTING_LOCAL_DATABASE' => '',
            'APP_CONFIG_CACHE' => 'storage/app/qudrat-local/unused-test-config.php',
            'APP_SERVICES_CACHE' => 'storage/app/qudrat-local/services.php', 'APP_PACKAGES_CACHE' => 'storage/app/qudrat-local/packages.php',
        ]);
        foreach ($values as $name => $value) {
            putenv($name.'='.$value);
            $_ENV[$name] = $_SERVER[$name] = $value;
        }
        $app = require dirname(__DIR__).'/bootstrap/app.php';
        $app->useEnvironmentPath($runtime);
        $app->make(Kernel::class)->bootstrap();
        $app->make(ImportEnvironment::class)->assertSafe();
        if ($app['db']->connection()->getDatabaseName() !== 'yarub_qudrat_test_local') {
            throw new \RuntimeException('Refusing to initialize an unspecified test database.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();
        // Migrate only the new module and its existing user prerequisite, not the historical app schema.
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2014_10_12_000000_create_users_table.php',
            'database/migrations/2026_10_21_000001_create_qudrat_tables.php',
            'database/migrations/2026_10_21_000002_publish_qudrat_content_by_default.php',
            'database/migrations/2026_10_29_000001_add_set_identity_to_qudrat_sessions.php',
        ], '--force' => true])->assertExitCode(0);
        DB::beginTransaction();
    }

    protected function tearDown(): void
    {
        if ($this->app) {
            while (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
        }
        parent::tearDown();
    }
}
