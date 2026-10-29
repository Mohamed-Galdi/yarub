<?php

namespace Tests;

use Illuminate\Support\Facades\DB;

abstract class ArabicSkillsTestCase extends QudratTestCase
{
    protected function setUp(): void
    {
        // Reuse Qudrat's guarded disposable connection, not its application migrations.
        TestCase::setUp();
        $this->assertSame('yarub_qudrat_test_local', DB::connection()->getDatabaseName());
        config(['arabic_skills.allow_development_import' => true]);
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2014_10_12_000000_create_users_table.php',
            'database/migrations/2026_10_23_000001_create_arabic_skill_tables.php',
            'database/migrations/2026_10_29_000002_add_set_identity_to_arabic_skill_sessions.php',
        ], '--force' => true])->assertExitCode(0);
        DB::beginTransaction();
    }
}
