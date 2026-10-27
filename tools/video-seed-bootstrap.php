<?php

$app = require __DIR__.'/qudrat-bootstrap.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
// Override any previously selected review database before opening a connection.
config(['database.default' => 'mysql', 'database.connections.mysql' => array_merge(config('database.connections.mysql'), [
    'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 33317, 'database' => 'yarub_qudrat_dev_local',
    'username' => 'qudrat_local', 'password' => 'qudrat-local-only', 'url' => null, 'unix_socket' => '', 'read' => null, 'write' => null,
]), 'qudrat.existing_local_database' => '']);
Illuminate\Support\Facades\DB::purge('mysql');
$app->make(App\Services\Qudrat\ImportEnvironment::class)->assertSafe();
if (! $app->environment('local') || config('database.connections.mysql.database') !== 'yarub_qudrat_dev_local'
    || (int) config('database.connections.mysql.port') !== 33317) throw new RuntimeException('Video seed review requires the dedicated local development database.');
config(['filesystems.disks.video_library.root' => storage_path('app/qudrat-local/video-seed/private'),
    'video_library.minimum_free_bytes' => 0, 'video_library.free_space_reserve_ratio' => 0]);

return $app;
