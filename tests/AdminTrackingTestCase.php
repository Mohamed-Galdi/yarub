<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

abstract class AdminTrackingTestCase extends QudratTestCase
{
    protected array $additionalMigrations = [];

    public function createApplication(): \Illuminate\Foundation\Application
    {
        $app = parent::createApplication();
        $connection = $app['db']->connection();
        if ($connection->getConfig('host') !== '127.0.0.1'
            || (int) $connection->getConfig('port') !== 33317
            || $connection->getDatabaseName() !== 'yarub_qudrat_test_local') {
            throw new \RuntimeException('Admin tracking tests require the dedicated loopback test database on port 33317.');
        }
        if ($connection->selectOne('SELECT DATABASE() AS name')->name !== 'yarub_qudrat_test_local') {
            throw new \RuntimeException('Unexpected connected test database.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        TestCase::setUp();
        $this->artisan('migrate', ['--path' => array_merge([
            'database/migrations/2014_10_12_000000_create_users_table.php',
            'database/migrations/2026_10_21_000001_create_qudrat_tables.php',
            'database/migrations/2026_10_21_000002_publish_qudrat_content_by_default.php',
            'database/migrations/2026_10_23_000001_create_arabic_skill_tables.php',
            'database/migrations/2026_10_29_000001_add_set_identity_to_qudrat_sessions.php',
            'database/migrations/2026_10_29_000002_add_set_identity_to_arabic_skill_sessions.php',
        ], $this->additionalMigrations), '--force' => true])->assertExitCode(0);
        DB::beginTransaction();
    }
}
