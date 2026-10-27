<?php

namespace Tests\Feature\VideoLibrary;

use App\Models\ArabicSkillBank;
use App\Models\LibraryVideo;
use App\Models\User;
use App\Models\VideoLibrarySetting;
use App\Services\VideoLibrary\SeedPackage;
use App\Services\VideoLibrary\VideoManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Tests\VideoLibraryTestCase;

class SeedImportTest extends VideoLibraryTestCase
{
    private string $root;
    private User $admin;
    private SeedPackage $seeds;
    private string $base = 'برهان - قلم يعرب - افتتاحية تجذب القارئ';

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = storage_path('app/qudrat-local/video-tests/'.Str::uuid());
        File::makeDirectory($this->root.'/package', 0750, true);
        config(['filesystems.disks.video_library.root' => $this->root.'/private', 'video_library.minimum_free_bytes' => 0, 'video_library.free_space_reserve_ratio' => 0]);
        $this->admin = User::create(['name' => 'Seed admin', 'email' => 'seed-admin@example.invalid', 'password' => 'local', 'role' => 'admin']);
        ArabicSkillBank::create(['slug' => 'burhan', 'title' => 'بُرهان', 'subtitle' => 'محلي', 'sort_order' => 5, 'source_metadata' => []]);
        (new Process(['ffmpeg', '-nostdin', '-v', 'error', '-f', 'lavfi', '-i', 'testsrc2=size=180x320:rate=15', '-t', '1', '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', $this->root.'/package/'.$this->base.'.mp4']))->setTimeout(30)->mustRun();
        $cover = imagecreatetruecolor(180, 320);
        imagejpeg($cover, $this->root.'/package/'.$this->base.'.jpg');
        imagedestroy($cover);
        $this->seeds = app(SeedPackage::class);
    }

    protected function tearDown(): void
    {
        try { parent::tearDown(); }
        finally { if (isset($this->root) && str_starts_with($this->root, storage_path('app/qudrat-local/video-tests/'))) File::deleteDirectory($this->root); }
    }

    private function runImport(): array
    {
        return $this->seeds->import($this->seeds->scan($this->root.'/package'), $this->admin, fn () => null);
    }

    public function test_import_and_repeat_preserve_identity_custom_cover_admin_edits_and_example(): void
    {
        $this->assertSame(['imported' => 1, 'skipped' => 0], $this->runImport());
        $video = LibraryVideo::firstOrFail();
        $this->assertSame('قلم يعرب - افتتاحية تجذب القارئ', $video->title);
        $this->assertSame('burhan', $video->arabicSkillBank()->first()->slug);
        $this->assertSame('custom', $video->cover_origin);
        $this->assertTrue($video->isEligible());
        $this->assertNull(VideoLibrarySetting::find(1)->public_example_video_id);
        $video->update(['title' => 'تعديل المسؤول']);
        app(VideoManager::class)->visibility($this->admin, $video, false);
        $this->assertSame(['imported' => 0, 'skipped' => 1], $this->runImport());
        $this->assertSame(1, LibraryVideo::count());
        $this->assertSame('تعديل المسؤول', $video->fresh()->title);
        $this->assertFalse($video->fresh()->is_visible);
    }

    public function test_changed_source_is_a_conflict_not_an_overwrite_or_duplicate(): void
    {
        $this->runImport();
        file_put_contents($this->root.'/package/'.$this->base.'.jpg', 'changed', FILE_APPEND);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Source changed');
        try { $this->runImport(); }
        finally { $this->assertSame(1, LibraryVideo::count()); }
    }

    public function test_deleted_seed_remains_deleted_on_repeat(): void
    {
        $this->runImport();
        $video = LibraryVideo::firstOrFail();
        app(VideoManager::class)->delete($this->admin, $video, $video->title);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Previously deleted');
        try { $this->runImport(); }
        finally { $this->assertSame(0, LibraryVideo::count()); }
    }

    public function test_all_classifications_preflight_before_writing(): void
    {
        copy($this->root.'/package/'.$this->base.'.mp4', $this->root.'/package/رسم - مثال.mp4');
        copy($this->root.'/package/'.$this->base.'.jpg', $this->root.'/package/رسم - مثال.jpg');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing/inactive');
        try { $this->runImport(); }
        finally { $this->assertSame(0, DB::table('video_seed_imports')->count()); }
    }

    public function test_unpaired_files_rejected_before_import(): void
    {
        unlink($this->root.'/package/'.$this->base.'.jpg');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Missing/unsafe pair');
        $this->runImport();
    }

    public function test_dry_run_is_read_only_and_local_cannot_use_production_override(): void
    {
        DB::connection()->enableQueryLog();
        $this->artisan('video-library:import', ['--path' => $this->root.'/package', '--dry-run' => true])->assertExitCode(0);
        $this->assertSame([], DB::connection()->getQueryLog());
        DB::connection()->disableQueryLog();
        $this->artisan('video-library:import', ['--path' => $this->root.'/package', '--production' => true, '--database' => 'yarub_qudrat_test_local', '--sha256' => $this->seeds->scan($this->root.'/package')['digest']])->assertExitCode(1);
        $this->assertSame(0, LibraryVideo::count());
    }
}
