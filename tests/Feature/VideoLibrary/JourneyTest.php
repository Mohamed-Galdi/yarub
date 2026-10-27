<?php

namespace Tests\Feature\VideoLibrary;

use App\Http\Middleware\LogVisit;
use App\Models\ContactPage;
use App\Models\LibraryVideo;
use App\Models\User;
use App\Models\VideoLibrarySetting;
use App\Services\VideoLibrary\MediaFiles;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use Tests\VideoLibraryTestCase;

class JourneyTest extends VideoLibraryTestCase
{
    private string $root;
    private User $admin;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        $this->root = storage_path('app/qudrat-local/video-tests/'.Str::uuid());
        File::makeDirectory($this->root.'/backup', 0750, true);
        config(['filesystems.disks.video_library.root' => $this->root.'/private', 'video_library.minimum_free_bytes' => 0, 'video_library.free_space_reserve_ratio' => 0]);
        $this->admin = User::create(['name' => 'Journey admin', 'email' => 'journey-admin@example.invalid', 'password' => 'local', 'role' => 'admin']);
        $this->student = User::create(['name' => 'Journey student', 'email' => 'journey-student@example.invalid', 'password' => 'local', 'role' => 'student']);
        ContactPage::firstOrCreate([], array_fill_keys(['commercial_registration_no', 'phone_number', 'email', 'address', 'whatsapp_number', 'instagram', 'tiktok', 'snapchat'], 'local-only'));
        (new Process(['ffmpeg', '-nostdin', '-v', 'error', '-f', 'lavfi', '-i', 'testsrc2=size=180x320:rate=15', '-t', '2', '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-profile:v', 'main', '-level:v', '3.0', '-movflags', '+faststart', '-y', $this->root.'/fixture.mp4']))->setTimeout(30)->mustRun();
    }

    protected function tearDown(): void
    {
        try { parent::tearDown(); }
        finally {
            if (isset($this->root) && str_starts_with($this->root, storage_path('app/qudrat-local/video-tests/'))) File::deleteDirectory($this->root);
        }
    }

    private function upload(): LibraryVideo
    {
        $response = $this->actingAs($this->admin)->postJson(route('admin.videos.store'), [
            'title' => 'كيف نقرأ المعنى؟', 'qudrat_category_id' => $this->category->id, 'request_key' => (string) Str::uuid(),
            'video' => new UploadedFile($this->root.'/fixture.mp4', 'fixture.mp4', 'video/mp4', null, true),
        ])->assertOk()->assertJsonPath('video.processing_state', 'ready')->assertJsonPath('is_public_example', false);

        return LibraryVideo::findOrFail($response->json('video.id'));
    }

    private function bytes($response): string
    {
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
        ob_start(); $response->baseResponse->sendContent();

        return ob_get_clean();
    }

    public function test_complete_http_journey_from_upload_to_login_share_revocation_and_delete(): void
    {
        $video = $this->upload();
        $files = app(MediaFiles::class);
        $source = file_get_contents($files->path($video->video_path));
        $paths = [$video->video_path, $video->cover_path];
        $published = $video->published_at;
        $studentAsset = route('student.videos.asset', [$video, 'media']);
        $publicAsset = route('videos.example.asset', 'media');
        Auth::logout();
        $this->get(route('student.videos.catalog'))->assertUnauthorized();
        $this->get($studentAsset)->assertUnauthorized();
        $this->get($publicAsset)->assertNotFound();
        $shared = route('student.videos.index', ['video' => $video->id]);
        $this->get($shared)->assertRedirect(route('login'))->assertSessionHas('url.intended', $shared);
        $this->post(route('student.login'), ['email' => $this->student->email, 'password' => 'local'])->assertRedirect($shared);
        $this->get(route('student.videos.catalog', ['q' => 'المعنى', 'type' => 'q:'.$this->category->id]))->assertOk()->assertJsonPath('total', 1);
        $this->get(route('student.videos.show', $video))->assertOk()->assertJsonPath('share_url', $shared);
        $this->assertSame(substr($source, 10, 32), $this->bytes($this->get($studentAsset, ['Range' => 'bytes=10-41'])->assertStatus(206)->assertHeader('Content-Range', 'bytes 10-41/'.strlen($source))));
        $this->get(route('student.videos.asset', [$video, 'cover']))->assertOk()->assertHeader('Content-Type', 'image/jpeg');

        // A separate recipient authentication roundtrip does not grant anonymous access.
        Auth::logout();
        $this->get($shared)->assertRedirect(route('login'));
        $this->post(route('student.login'), ['email' => $this->student->email, 'password' => 'local'])->assertRedirect($shared);
        $this->get(route('student.videos.show', $video))->assertOk();
        $this->actingAs($this->admin)->patchJson(route('admin.videos.visibility', $video), ['is_visible' => false])->assertOk();
        $this->actingAs($this->student)->get(route('student.videos.show', $video))->assertNotFound();
        $this->get($studentAsset)->assertNotFound();
        $this->get(route('student.videos.catalog'))->assertJsonPath('total', 0);
        $this->actingAs($this->admin)->patchJson(route('admin.videos.visibility', $video), ['is_visible' => true])->assertOk();
        $this->assertTrue($published->equalTo($video->fresh()->published_at));
        $this->putJson(route('admin.videos.public-example'), ['video_id' => $video->id])->assertNoContent();
        Auth::logout();
        $this->get(route('videos'))->assertOk()->assertSee($video->title)->assertSee('سجّل الدخول لمشاهدة جميع الفيديوهات');
        $this->assertSame(substr($source, -24), $this->bytes($this->get($publicAsset, ['Range' => 'bytes=-24'])->assertStatus(206)));
        $this->get(route('videos.example.asset', 'cover'))->assertOk();
        $this->get($studentAsset)->assertUnauthorized();
        $this->get(route('student.videos.show', $video))->assertUnauthorized();
        $this->actingAs($this->admin)->putJson(route('admin.videos.public-example'), ['video_id' => null])->assertNoContent();
        Auth::logout();
        $this->get($publicAsset)->assertNotFound();
        $this->get(route('videos.example.asset', 'cover'))->assertNotFound();
        $this->get(route('videos'))->assertDontSee('<video', false);
        $this->actingAs($this->admin)->deleteJson(route('admin.videos.destroy', $video), ['confirmation' => $video->title])->assertNoContent();
        $this->assertNull(LibraryVideo::find($video->id));
        $this->artisan('video-library:cleanup')->assertExitCode(0);
        foreach ($paths as $path) $this->assertFalse($files->disk()->exists($path));
        $this->actingAs($this->student)->get($studentAsset)->assertNotFound();
    }

    public function test_paired_local_snapshot_restore_preserves_identity_bytes_example_and_cleanup_safety(): void
    {
        $video = $this->upload();
        $this->putJson(route('admin.videos.public-example'), ['video_id' => $video->id])->assertNoContent();
        $files = app(MediaFiles::class);
        $row = (array) DB::table('library_videos')->find($video->id);
        $operations = DB::table('video_media_operations')->where('video_id', $video->id)->get()->map(fn ($item) => (array) $item)->all();
        $hashes = [];
        foreach ([$video->video_path, $video->cover_path] as $index => $path) {
            File::copy($files->path($path), $this->root.'/backup/'.$index);
            $hashes[$path] = hash_file('sha256', $files->path($path));
        }
        $this->deleteJson(route('admin.videos.destroy', $video), ['confirmation' => $video->title])->assertNoContent();
        $this->artisan('video-library:cleanup')->assertExitCode(0);
        foreach ($hashes as $path => $hash) $this->assertFalse($files->disk()->exists($path));
        // Isolated restore drill only: retain the same IDs, relative paths and operation tombstones.
        foreach (array_keys($hashes) as $index => $path) {
            $files->reserve($path);
            File::copy($this->root.'/backup/'.$index, $files->path($path, false));
        }
        DB::table('library_videos')->insert($row);
        foreach ($operations as $operation) DB::table('video_media_operations')->where('request_key', $operation['request_key'])->update($operation);
        DB::table('video_library_settings')->where('id', 1)->update(['public_example_video_id' => $video->id]);
        Storage::forgetDisk('video_library');
        foreach ($hashes as $path => $hash) $this->assertSame($hash, hash_file('sha256', app(MediaFiles::class)->path($path)));
        $this->assertTrue(LibraryVideo::findOrFail($video->id)->isEligible());
        $this->assertSame($video->id, VideoLibrarySetting::eligiblePublicExample()->id);
        $this->travel(2)->hours();
        $this->artisan('video-library:cleanup')->assertExitCode(0);
        foreach ($hashes as $path => $hash) $this->assertTrue($files->disk()->exists($path));
        Auth::logout();
        $this->get(route('videos.example.asset', 'media'), ['Range' => 'bytes=0-15'])->assertStatus(206);
        $this->actingAs($this->student)->get(route('student.videos.show', $video))->assertOk()->assertJsonPath('id', $video->id);
    }
}
