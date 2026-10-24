<?php

namespace App\Services\ArabicSkills;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportEnvironment
{
    public function assertSafe(?ProductionImportApproval $approval = null, ?string $digest = null): void
    {
        if ($approval !== null) {
            $this->productionTarget($approval, $digest);
            // Check the actual selected schema as well as Laravel's connection configuration.
            $actual = DB::connection()->selectOne('SELECT DATABASE() AS database_name');
            if (($actual->database_name ?? null) !== $approval->database) {
                throw new RuntimeException('The connected database differs from the explicitly approved target.');
            }

            return;
        }
        if (! app()->environment(['local', 'development', 'testing']) || ! config('arabic_skills.allow_development_import')) {
            throw new RuntimeException('ArabicSkills imports require an opted-in local/development/testing environment.');
        }

        // Inspect config before opening a connection; a URL or socket could bypass host restrictions.
        $connection = DB::connection();
        $config = $connection->getConfig();
        $raw = config('database.connections.'.DB::getDefaultConnection(), []);
        $approvedExisting = app()->environment(['local', 'development'])
            && ($config['database'] ?? null) === 'yarub'
            && config('arabic_skills.existing_local_database') === 'yarub';
        // Reuse the existing restricted, disposable Qudrat test instance only in testing.
        $approvedTest = app()->environment('testing')
            && ($config['database'] ?? null) === 'yarub_qudrat_test_local'
            && (int) ($config['port'] ?? 0) === 33317;
        if (($config['driver'] ?? null) !== 'mysql'
            || ! in_array($config['host'] ?? null, ['127.0.0.1', 'localhost', '::1'], true)
            || ! empty($config['url']) || ! empty($config['unix_socket'])
            || ! empty($config['read']) || ! empty($config['write'])
            || ! empty($raw['url']) || ! empty($raw['unix_socket']) || ! empty($raw['read']) || ! empty($raw['write'])
            || (! $approvedExisting && ! $approvedTest && preg_match('/^yarub_arabic_skills_(dev|test)_[a-z0-9_]+$/D', $config['database'] ?? '') !== 1)) {
            throw new RuntimeException('Use a dedicated loopback MySQL database named yarub_arabic_skills_dev_* or yarub_arabic_skills_test_*, or explicitly opt in the existing local yarub database.');
        }
    }

    /** Validate production intent before opening a connection or asking for confirmation. */
    public function productionTarget(ProductionImportApproval $approval, ?string $digest): array
    {
        // A one-process opt-in also works with cached Laravel config, without editing production .env.
        $optedIn = config('arabic_skills.allow_production_import') || getenv('ARABIC_SKILLS_ALLOW_PRODUCTION_IMPORT') === 'true';
        if (! app()->environment('production') || ! app()->runningInConsole() || ! $optedIn) {
            throw new RuntimeException('Production import requires the console, APP_ENV=production and ARABIC_SKILLS_ALLOW_PRODUCTION_IMPORT=true.');
        }
        if ($approval->database === '' || trim($approval->database) !== $approval->database) {
            throw new RuntimeException('Supply the exact production database name with --database.');
        }
        if (preg_match('/^[a-f0-9]{64}$/D', $approval->digest) !== 1 || $digest === null || ! hash_equals($digest, $approval->digest)) {
            throw new RuntimeException('The --sha256 digest does not match the validated content. Review the local dry-run digest before retrying.');
        }
        $raw = config('database.connections.'.DB::getDefaultConnection(), []);
        // Laravel removes read/write entries when it constructs the resolved connection.
        if (! empty($raw['url']) || ! empty($raw['unix_socket']) || ! empty($raw['read']) || ! empty($raw['write'])) {
            throw new RuntimeException('Production target must use one explicitly configured MySQL connection; URL, socket and read/write overrides are not supported.');
        }
        $config = DB::connection()->getConfig();
        if (($config['driver'] ?? null) !== 'mysql' || ($config['database'] ?? null) !== $approval->database
            || empty($config['host']) || ! is_string($config['host'])
            || ! empty($config['url']) || ! empty($config['unix_socket']) || ! empty($config['read']) || ! empty($config['write'])) {
            throw new RuntimeException('Production target must match --database on one explicitly configured MySQL connection; URL, socket and read/write overrides are not supported.');
        }

        return ['host' => $config['host'], 'port' => $config['port'] ?? 3306, 'database' => $config['database']];
    }
}
