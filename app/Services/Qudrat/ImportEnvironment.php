<?php

namespace App\Services\Qudrat;

use Illuminate\Support\Facades\DB;
use RuntimeException;

class ImportEnvironment
{
    public function assertSafe(): void
    {
        if (! app()->environment(['local', 'development', 'testing']) || ! config('qudrat.allow_development_import')) {
            throw new RuntimeException('Qudrat imports require an opted-in isolated local/development/testing environment.');
        }

        // Inspect config before opening a connection; a URL or socket could bypass host restrictions.
        $connection = DB::connection();
        $config = $connection->getConfig();
        if (($config['driver'] ?? null) !== 'mysql'
            || ! in_array($config['host'] ?? null, ['127.0.0.1', 'localhost', '::1'], true)
            || ! empty($config['url']) || ! empty($config['unix_socket'])
            || ! empty($config['read']) || ! empty($config['write'])
            || preg_match('/^yarub_qudrat_(dev|test)_[a-z0-9_]+$/D', $config['database'] ?? '') !== 1) {
            throw new RuntimeException('Use a dedicated loopback MySQL database named yarub_qudrat_dev_* or yarub_qudrat_test_*.');
        }
    }
}
