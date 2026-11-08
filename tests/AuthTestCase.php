<?php

namespace Tests;

use App\Http\Middleware\LogVisit;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class AuthTestCase extends TestCase
{
    use RefreshDatabase;

    protected function migrateFreshUsing(): array
    {
        if (! app()->environment('testing') || config('database.default') !== 'sqlite'
            || config('database.connections.sqlite.database') !== ':memory:') {
            throw new \RuntimeException('Authentication tests require testing + SQLite :memory:.');
        }
        return ['--path' => [
            'database/migrations/2014_10_12_000000_create_users_table.php',
            'database/migrations/2014_10_12_100000_create_password_reset_tokens_table.php',
            'database/migrations/2026_11_06_000001_create_social_identities_table.php',
        ]];
    }

    protected function migrateUsing(): array
    {
        return $this->migrateFreshUsing();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(LogVisit::class);
        $this->withoutVite();
        config(['services.google' => [
            'client_id' => 'test-client', 'client_secret' => 'test-secret',
            'redirect' => 'http://localhost/auth/google/callback',
        ]]);
    }
}
