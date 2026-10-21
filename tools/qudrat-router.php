<?php

// Native PHP review server. Routes through explicit local configuration, including /index.php.
if (PHP_SAPI !== 'cli-server') {
    http_response_code(404);
    exit;
}
$public = realpath(__DIR__.'/../public');
$path = realpath($public.'/'.rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
if ($path && str_starts_with($path, $public.DIRECTORY_SEPARATOR) && is_file($path)
    && in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['css', 'js', 'map', 'svg', 'png', 'jpg', 'jpeg', 'webp', 'ico', 'woff', 'woff2', 'ttf', 'otf'], true)) {
    return false;
}
$app = require __DIR__.'/qudrat-bootstrap.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();
$app->make(App\Services\Qudrat\ImportEnvironment::class)->assertSafe();
foreach (['sessions', 'views', 'cache'] as $directory) {
    if (! is_dir($runtime.'/'.$directory)) {
        mkdir($runtime.'/'.$directory, 0777, true);
    }
}
$connectionIdentity = hash('sha256', json_encode(array_intersect_key(
    $app['db']->connection()->getConfig(), array_flip(['host', 'port', 'database'])
)));
config(['session.driver' => 'file', 'session.files' => $runtime.'/sessions', 'session.cookie' => 'yarub_qudrat_review_'.substr($connectionIdentity, 0, 12),
    'view.compiled' => $runtime.'/views', 'cache.default' => 'file', 'cache.stores.file.path' => $runtime.'/cache',
    'app.url' => 'http://127.0.0.1:8017']);
Illuminate\Support\Facades\Vite::useHotFile($runtime.'/vite.hot');
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
$response->send();
$kernel->terminate($request, $response);
