<?php

// Files only: no Laravel environment or database. Originals are never modified.
require __DIR__.'/../vendor/autoload.php';

use App\Services\VideoLibrary\SeedPackage;
use Symfony\Component\Process\Process;

$source = $argv[1] ?? __DIR__.'/../vids';
$destination = $argv[2] ?? __DIR__.'/../storage/app/qudrat-local/video-seed/package';
if (file_exists($destination)) throw new RuntimeException('Destination already exists; use a new output directory.');
$package = (new SeedPackage)->scan($source);
if (! mkdir($destination, 0750, true)) throw new RuntimeException('Cannot create package directory.');
foreach ($package['items'] as $item) {
    $input = $package['root'].'/'.$item['filename'];
    $probe = new Process(['ffprobe', '-v', 'error', '-select_streams', 'v:0', '-show_entries', 'stream=width,height,sample_aspect_ratio', '-of', 'json', $input]);
    $probe->setTimeout(15)->mustRun();
    $dimensions = json_decode($probe->getOutput(), true, 32, JSON_THROW_ON_ERROR)['streams'][0];
    $output = $destination.'/'.$item['filename'];
    $sar = $dimensions['sample_aspect_ratio'] ?? '1:1';
    if ($sar !== '1:1' && $sar !== 'N/A') {
        // Preserve display proportions while converting anamorphic pixels to square pixels.
        [$num, $den] = array_map('intval', explode(':', $sar));
        if ($den <= 0 || $num <= 0 || $dimensions['width'] * $num / $den >= $dimensions['height']) throw new RuntimeException('Non-portrait display aspect requires explicit review.');
        echo 'Normalizing pixel aspect without cropping: '.$item['filename'].PHP_EOL;
        $process = new Process(['ffmpeg', '-nostdin', '-v', 'error', '-i', $input, '-map', '0:v:0', '-map', '0:a?',
            '-vf', 'scale=trunc(iw*sar/2)*2:ih,setsar=1', '-c:v', 'libx264', '-preset', 'fast', '-crf', '18', '-pix_fmt', 'yuv420p', '-profile:v', 'high', '-level:v', '4.2', '-c:a', 'copy', '-movflags', '+faststart', $output]);
        $process->setTimeout(300)->mustRun();
    } elseif ($dimensions['height'] > $dimensions['width']) {
        // Move the index before media bytes without re-encoding the original tracks.
        (new Process(['ffmpeg', '-nostdin', '-v', 'error', '-i', $input, '-map', '0:v:0', '-map', '0:a?', '-c', 'copy', '-movflags', '+faststart', $output]))->setTimeout(120)->mustRun();
    } else {
        throw new RuntimeException('Landscape input requires explicit content review: '.$item['filename']);
    }
    if (! copy($package['root'].'/'.$item['cover'], $destination.'/'.$item['cover'])) throw new RuntimeException('Cover copy failed.');
}
$prepared = (new SeedPackage)->scan($destination);
file_put_contents(dirname($destination).'/package.sha256', $prepared['digest'].PHP_EOL);
echo count($prepared['items']).' pairs; SHA-256 '.$prepared['digest'].PHP_EOL;
