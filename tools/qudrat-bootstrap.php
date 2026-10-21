<?php

require_once __DIR__.'/../vendor/autoload.php';

$root = dirname(__DIR__);
$runtime = $root.'/storage/app/qudrat-local';
if (! is_file($runtime.'/.env')) {
    throw new RuntimeException('Run tools/qudrat-local.ps1 start first.');
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

return $app;
