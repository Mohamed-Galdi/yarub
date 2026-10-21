<?php

// Dedicated native development entry point. Never loads the project's real .env.
require __DIR__.'/../vendor/autoload.php';

$root = dirname(__DIR__);
$runtime = $root.'/storage/app/qudrat-local';
$command = $_SERVER['argv'][1] ?? '';
if (! in_array($command, ['qudrat:import', 'migrate', 'migrate:status'], true)
    || in_array('--seed', $_SERVER['argv'], true)) {
    fwrite(STDERR, "This isolated entry point supports only Qudrat import and migrations, without seeding.\n");
    exit(1);
}
if (! is_file($runtime.'/.env')) {
    fwrite(STDERR, "Run tools/qudrat-local.ps1 start first.\n");
    exit(1);
}
foreach (Dotenv\Dotenv::createArrayBacked($runtime)->load() as $name => $value) {
    putenv($name.'='.$value);
    $_ENV[$name] = $_SERVER[$name] = $value;
}
foreach (['APP_CONFIG_CACHE' => 'storage/app/qudrat-local/unused-config.php', 'APP_SERVICES_CACHE' => 'storage/app/qudrat-local/services.php',
    'APP_PACKAGES_CACHE' => 'storage/app/qudrat-local/packages.php', 'APP_ROUTES_CACHE' => 'storage/app/qudrat-local/routes.php'] as $name => $value) {
    putenv($name.'='.$value);
    $_ENV[$name] = $_SERVER[$name] = $value;
}

$app = require $root.'/bootstrap/app.php';
$app->useEnvironmentPath($runtime);
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
app(App\Services\Qudrat\ImportEnvironment::class)->assertSafe();
$input = new Symfony\Component\Console\Input\ArgvInput;
$output = new Symfony\Component\Console\Output\ConsoleOutput;
$status = $kernel->handle($input, $output);
$kernel->terminate($input, $status);
exit($status);
