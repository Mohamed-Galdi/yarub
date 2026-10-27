<?php

if (PHP_SAPI !== 'cli-server' || ! in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) { http_response_code(404); exit; }
$public = realpath(__DIR__.'/../public');
$path = realpath($public.'/'.rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));
if ($path && str_starts_with($path, $public.DIRECTORY_SEPARATOR) && is_file($path) && in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), ['css', 'js', 'map', 'svg', 'png', 'jpg', 'jpeg', 'webp', 'ico', 'woff', 'woff2', 'ttf', 'otf'], true)) return false;
require __DIR__.'/../vendor/autoload.php';
$case = new class('review-server') extends Tests\VideoLibraryTestCase {};
$app = $case->createApplication();
$runtime = storage_path('app/qudrat-local/video-admin-review');
if (! is_file($runtime.'/fixture.json')) { http_response_code(503); exit('Prepare disposable video review fixtures first.'); }
$fixture = json_decode(file_get_contents($runtime.'/fixture.json'), true, 32, JSON_THROW_ON_ERROR);
foreach (['sessions', 'views'] as $directory) if (! is_dir($runtime.'/'.$directory)) mkdir($runtime.'/'.$directory, 0750, true);
config(['app.url' => 'http://127.0.0.1:8026', 'session.driver' => 'file', 'session.files' => $runtime.'/sessions', 'session.cookie' => 'yarub_video_admin_review',
    'view.compiled' => $runtime.'/views', 'filesystems.disks.video_library.root' => $runtime.'/private', 'video_library.minimum_free_bytes' => 0, 'video_library.free_space_reserve_ratio' => 0]);
Illuminate\Support\Facades\Vite::useHotFile($runtime.'/vite.hot');
// Synthetic reviewer, local loopback and exact disposable schema only. Production auth is unchanged.
if (($fixture['auth_mode'] ?? '') !== 'session') Illuminate\Support\Facades\Auth::onceUsingId(($fixture['auth_mode'] ?? '') === 'admin' ? $fixture['admin_id'] : ($fixture['student_id'] ?? $fixture['admin_id']));
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle($request = Illuminate\Http\Request::capture());
$response->send(); $kernel->terminate($request, $response);
