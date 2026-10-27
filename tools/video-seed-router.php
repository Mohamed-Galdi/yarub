<?php

if (PHP_SAPI !== 'cli-server' || ! in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) { http_response_code(404); exit; }
$public = realpath(__DIR__.'/../public');
$path = realpath($public.'/'.rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
if ($path && str_starts_with($path, $public.DIRECTORY_SEPARATOR) && is_file($path)
    && in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['css', 'js', 'map', 'svg', 'png', 'jpg', 'jpeg', 'webp', 'ico', 'woff', 'woff2', 'ttf', 'otf'], true)) return false;
$app = require __DIR__.'/video-seed-bootstrap.php';
$runtime = storage_path('app/qudrat-local/video-seed');
foreach (['sessions', 'views'] as $directory) if (! is_dir($runtime.'/'.$directory)) mkdir($runtime.'/'.$directory, 0750, true);
config(['session.driver' => 'file', 'session.files' => $runtime.'/sessions', 'session.cookie' => 'yarub_video_seed_review', 'view.compiled' => $runtime.'/views', 'app.url' => 'http://127.0.0.1:8027']);
Illuminate\Support\Facades\Vite::useHotFile($runtime.'/vite.hot');
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
$response->send(); $kernel->terminate($request, $response);
