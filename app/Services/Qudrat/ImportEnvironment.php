<?php

namespace App\Services\Qudrat;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportEnvironment
{
    public function assertSafe(): void
    {
        if (! app()->environment(['local', 'development', 'testing']) || ! config('qudrat.allow_development_import')) {
            throw new RuntimeException('Qudrat imports require an opted-in local/development/testing environment.');
        }

        // Inspect config before opening a connection; a URL or socket could bypass host restrictions.
        $connection = DB::connection();
        $config = $connection->getConfig();
        $approvedExisting = app()->environment(['local', 'development'])
            && ($config['database'] ?? null) === 'yarub'
            && config('qudrat.existing_local_database') === 'yarub';
        if (($config['driver'] ?? null) !== 'mysql'
            || ! in_array($config['host'] ?? null, ['127.0.0.1', 'localhost', '::1'], true)
            || ! empty($config['url']) || ! empty($config['unix_socket'])
            || ! empty($config['read']) || ! empty($config['write'])
            || (! $approvedExisting && preg_match('/^yarub_qudrat_(dev|test)_[a-z0-9_]+$/D', $config['database'] ?? '') !== 1)) {
            throw new RuntimeException('Use a dedicated loopback MySQL database named yarub_qudrat_dev_* or yarub_qudrat_test_*, or explicitly opt in the existing local yarub database.');
        }
    }
}
