<?php

namespace App\Console\Commands;

use App\Services\VideoLibrary\MediaFiles;
use App\Services\VideoLibrary\VideoManager;
use Illuminate\Console\Command;

class VideoLibraryCleanup extends Command
{
    protected $signature = 'video-library:cleanup';

    protected $description = 'Recover interrupted video operations and retry scoped, unreferenced media cleanup';

    public function handle(VideoManager $manager, MediaFiles $files): int
    {
        $recovered = $manager->recoverInterrupted();
        $failed = $files->cleanup();
        $this->info("Recovered {$recovered} interrupted operations; {$failed} cleanup failures retained for retry.");

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
