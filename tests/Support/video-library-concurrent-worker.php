<?php

require __DIR__.'/../../vendor/autoload.php';

// Reuse the guarded test bootstrap, never the root application environment.
$case = new class('worker') extends Tests\VideoLibraryTestCase {};
$app = $case->createApplication();
$root = realpath($argv[1]);
$parent = realpath(storage_path('app/qudrat-local/video-tests'));
if (! $root || ! $parent || ! str_starts_with(str_replace('\\', '/', $root).'/', str_replace('\\', '/', $parent).'/')) {
    throw new RuntimeException('Refusing media outside the generated test fixture directory.');
}
config(['filesystems.disks.video_library.root' => $root.'/private', 'video_library.minimum_free_bytes' => 0, 'video_library.free_space_reserve_ratio' => 0]);
file_put_contents($root.'/ready-'.getmypid(), 'ready');
$deadline = microtime(true) + 10;
while (! is_file($root.'/go')) {
    if (microtime(true) > $deadline) {
        throw new RuntimeException('Concurrent worker barrier timed out.');
    }
    usleep(10000);
}
$admin = App\Models\User::findOrFail((int) $argv[2]);
$upload = new Illuminate\Http\UploadedFile($root.'/fixtures/portrait.mp4', 'portrait.mp4', 'video/mp4', null, true);
$video = $app->make(App\Services\VideoLibrary\VideoManager::class)->upload($admin,
    ['title' => 'رفع متزامن محلي', 'qudrat_category_id' => (int) $argv[3]], $upload, null, $argv[4]);
echo $video->id;
