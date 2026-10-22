<?php

// Separate processes exercise real MySQL row locks, only on the dedicated test database.
require __DIR__.'/../../vendor/autoload.php';
$runtime = dirname(__DIR__, 2).'/storage/app/qudrat-local';
$values = array_merge(Dotenv\Dotenv::createArrayBacked($runtime, '.env.testing')->load(), [
    'APP_ENV' => 'testing', 'DB_DATABASE' => 'yarub_qudrat_test_local', 'QUDRAT_EXISTING_LOCAL_DATABASE' => '',
    'APP_CONFIG_CACHE' => 'storage/app/qudrat-local/unused-test-config.php',
    'APP_SERVICES_CACHE' => 'storage/app/qudrat-local/services.php', 'APP_PACKAGES_CACHE' => 'storage/app/qudrat-local/packages.php',
]);
foreach ($values as $name => $value) {
    putenv($name.'='.$value);
    $_ENV[$name] = $_SERVER[$name] = $value;
}
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->useEnvironmentPath($runtime);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$app->make(App\Services\Qudrat\ImportEnvironment::class)->assertSafe();
if (Illuminate\Support\Facades\DB::connection()->getDatabaseName() !== 'yarub_qudrat_test_local') {
    throw new RuntimeException('Refusing an unspecified test database.');
}
$user = App\Models\User::findOrFail((int) $argv[2]);
$practice = $app->make(App\Services\Qudrat\Practice::class);
if ($argv[1] === 'start') {
    echo $practice->start($user, App\Models\QudratCategory::findOrFail((int) $argv[3]), 'practice')->id;
} else {
    $practice->visit($user, App\Models\QudratPracticeSession::findOrFail((int) $argv[3]), 'answer', (int) $argv[4], $argv[5]);
    echo 'graded';
}
