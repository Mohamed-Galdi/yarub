<?php

namespace Tests\Feature\VideoLibrary;

use App\Models\LibraryVideo;
use App\Models\User;
use App\Models\VideoLibrarySetting;
use App\Services\VideoLibrary\MediaFiles;
use App\Services\VideoLibrary\MediaInspector;
use App\Services\VideoLibrary\PublicExample;
use App\Services\VideoLibrary\VideoManager;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Process\Process;
use Tests\VideoLibraryTestCase;

class MediaTest extends VideoLibraryTestCase
{
    private string $root;
    private User $admin;
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root = storage_path('app/qudrat-local/video-tests/'.Str::uuid());
        File::makeDirectory($this->root.'/fixtures', 0750, true);
        config(['filesystems.disks.video_library.root' => $this->root.'/private',
            'video_library.minimum_free_bytes' => 0, 'video_library.free_space_reserve_ratio' => 0]);
        $this->admin = User::create(['name' => 'Local admin', 'email' => 'video-admin@example.invalid', 'password' => 'local', 'role' => 'admin']);
        $this->student = User::create(['name' => 'Local student', 'email' => 'video-student@example.invalid', 'password' => 'local', 'role' => 'student']);
    }

    protected function tearDown(): void
    {
        try {
            parent::tearDown();
        } finally {
            if (isset($this->root) && str_starts_with($this->root, storage_path('app/qudrat-local/video-tests/'))) {
                File::deleteDirectory($this->root);
            }
        }
    }

    private function fixture(string $name = 'portrait', array $extra = []): UploadedFile
    {
        $path = $this->root.'/fixtures/'.$name.'.mp4';
        if (! is_file($path)) {
            $process = new Process(array_merge([config('video_library.ffmpeg'), '-nostdin', '-v', 'error', '-f', 'lavfi',
                '-i', 'testsrc2=size=180x320:rate=15', '-f', 'lavfi', '-i', 'sine=frequency=440:sample_rate=44100',
                '-t', '1', '-c:v', 'libx264', '-pix_fmt', 'yuv420p', '-profile:v', 'main', '-level:v', '3.0',
                '-c:a', 'aac', '-ac', '1', '-movflags', '+faststart'], $extra, ['-y', $path]));
            $process->setTimeout(30)->mustRun();
        }

        return new UploadedFile($path, $name.'.mp4', 'video/mp4', null, true);
    }

    private function cover(): UploadedFile
    {
        $path = $this->root.'/fixtures/custom.png';
        $image = imagecreatetruecolor(180, 320);
        imagefill($image, 0, 0, imagecolorallocate($image, 41, 42, 101));
        imagepng($image, $path);
        imagedestroy($image);

        return new UploadedFile($path, 'custom.png', 'image/png', null, true);
    }

    private function metadata(): array
    {
        return ['title' => 'شرح قصير', 'description' => 'وصف عربي', 'qudrat_category_id' => $this->category->id];
    }

    private function upload(?string $key = null, ?UploadedFile $cover = null): LibraryVideo
    {
        return app(VideoManager::class)->upload($this->admin, $this->metadata(), $this->fixture(), $cover, $key ?? (string) Str::uuid());
    }

    private function bytes($response): string
    {
        $this->assertInstanceOf(BinaryFileResponse::class, $response->baseResponse);
        ob_start();
        $response->baseResponse->sendContent();

        return ob_get_clean();
    }

    public function test_real_upload_is_inspected_published_and_duplicate_request_is_replayed(): void
    {
        $key = (string) Str::uuid();
        $response = $this->actingAs($this->admin)->postJson(route('admin.videos.store'), $this->metadata() + ['request_key' => $key, 'video' => $this->fixture()]);
        $response->assertOk()->assertJsonPath('video.processing_state', 'ready')->assertJsonPath('processing', false)->assertJsonPath('is_public_example', false);
        $video = LibraryVideo::findOrFail($response->json('video.id'));
        $this->assertTrue($video->isEligible());
        $this->assertSame('generated', $video->cover_origin);
        $this->assertSame(180, $video->width);
        $this->assertSame(320, $video->height);
        $this->assertGreaterThan(0, $video->duration_seconds);
        $this->assertSame($this->fixture()->getSize(), $video->byte_size);
        $this->assertSame('h264', $video->codec_metadata['video']['codec_name']);
        $this->assertFileExists(app(MediaFiles::class)->path($video->cover_path));
        $this->assertNull(VideoLibrarySetting::eligiblePublicExample());
        $replayed = $this->upload($key);
        $this->assertSame($video->id, $replayed->id);
        $this->assertSame(1, LibraryVideo::count());
        $this->assertStringNotContainsString('video_path', $response->getContent());
    }

    public function test_replacement_preserves_identity_hidden_intent_publication_and_custom_cover(): void
    {
        $video = $this->upload(null, $this->cover());
        $old = $video->video_path;
        $custom = $video->cover_path;
        $published = $video->published_at;
        app(VideoManager::class)->visibility($this->admin, $video, false);
        app(VideoManager::class)->updateMetadata($this->admin, $video, $this->metadata() + ['unused' => 'ignored']);
        $updated = app(VideoManager::class)->replace($this->admin, $video, $this->fixture('replacement'), null, false, (string) Str::uuid());
        $this->assertSame($video->id, $updated->id);
        $this->assertFalse($updated->is_visible);
        $this->assertTrue($published->equalTo($updated->published_at));
        $this->assertSame($custom, $updated->cover_path);
        $this->assertSame('custom', $updated->cover_origin);
        $this->assertNotSame($old, $updated->video_path);
        app(MediaFiles::class)->cleanup();
        $this->assertFileDoesNotExist(app(MediaFiles::class)->path($old, false));
        $reset = app(VideoManager::class)->replace($this->admin, $updated, null, null, true, (string) Str::uuid());
        $this->assertSame('generated', $reset->cover_origin);
        $this->assertNotSame($custom, $reset->cover_path);
        $replacedCover = app(VideoManager::class)->replace($this->admin, $reset, null, $this->cover(), false, (string) Str::uuid());
        $this->assertSame('custom', $replacedCover->cover_origin);
        $this->assertSame('image/jpeg', mime_content_type(app(MediaFiles::class)->path($replacedCover->cover_path)));
    }

    public function test_corrupt_and_incompatible_files_fail_without_breaking_a_working_replacement(): void
    {
        $video = $this->upload();
        $oldPath = $video->video_path;
        $oldCover = $video->cover_path;
        foreach ([$this->fixture('wrong-codec', ['-c:v', 'mpeg4', '-profile:v', '0']),
            $this->fixture('landscape', ['-vf', 'scale=320:180']),
            $this->fixture('not-fast-start', ['-movflags', '-faststart'])] as $bad) {
            try {
                app(VideoManager::class)->replace($this->admin, $video, $bad, null, false, (string) Str::uuid());
                $this->fail('Incompatible video accepted.');
            } catch (ValidationException $e) {
                $this->assertArrayHasKey('video', $e->errors());
                $current = $video->fresh();
                $this->assertSame('ready', $current->processing_state);
                $this->assertSame($oldPath, $current->video_path);
                $this->assertSame($oldCover, $current->cover_path);
                $this->assertTrue($current->isEligible());
                $this->assertNotNull($current->processing_error);
            }
        }
        $corrupt = $this->fixture('truncated');
        $bytes = file_get_contents($corrupt->getPathname());
        file_put_contents($corrupt->getPathname(), substr($bytes, 0, -100));
        $this->actingAs($this->admin)->postJson(route('admin.videos.replace', $video), ['request_key' => (string) Str::uuid(), 'video' => $corrupt])->assertUnprocessable()->assertJsonValidationErrors('video');
        $this->assertSame($oldPath, $video->fresh()->video_path);
        $packets = $this->fixture('corrupt-packets');
        $bytes = file_get_contents($packets->getPathname());
        $offset = strpos($bytes, 'mdat') + 4;
        file_put_contents($packets->getPathname(), substr($bytes, 0, $offset).str_repeat("\0", strlen($bytes) - $offset));
        Log::spy();
        $this->postJson(route('admin.videos.replace', $video), ['request_key' => (string) Str::uuid(), 'video' => $packets])->assertUnprocessable()->assertJsonValidationErrors('video');
        $this->assertSame($oldPath, $video->fresh()->video_path);
    }

    public function test_failed_initial_upload_can_retry_same_request_without_duplicate_records(): void
    {
        $key = (string) Str::uuid();
        try {
            app(VideoManager::class)->upload($this->admin, $this->metadata(), $this->fixture('bad', ['-vf', 'scale=320:180']), null, $key);
            $this->fail('Invalid initial upload accepted.');
        } catch (ValidationException) {
            $failed = LibraryVideo::firstOrFail();
            $this->assertSame('failed', $failed->processing_state);
            $this->assertFalse($failed->isEligible());
            $this->assertNull($failed->published_at);
        }
        $retry = app(VideoManager::class)->upload($this->admin, ['title' => 'عنوان مصحح', 'arabic_skill_bank_id' => $this->bank->id], $this->fixture(), null, $key);
        $this->assertSame($failed->id, $retry->id);
        $this->assertSame('عنوان مصحح', $retry->title);
        $this->assertSame('arabic_skills', $retry->program);
        $this->assertSame(1, LibraryVideo::count());
        $this->assertTrue($retry->isEligible());
        app(VideoManager::class)->delete($this->admin, $retry, $retry->title);
        try {
            $this->upload($key);
            $this->fail('Deleted upload request resurrected content.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
            $this->assertSame(0, LibraryVideo::count());
        }
    }

    public function test_students_guests_admins_and_public_example_have_distinct_asset_access(): void
    {
        $video = $this->upload();
        $other = $this->upload();
        foreach (['media', 'cover'] as $asset) {
            $studentUrl = route('student.videos.asset', [$video, $asset]);
            $adminUrl = route('admin.videos.preview', [$video, $asset]);
            $publicUrl = route('videos.example.asset', $asset);
            $this->get($studentUrl)->assertUnauthorized()->assertHeader('Cache-Control', 'no-store, private');
            $this->get($adminUrl)->assertUnauthorized();
            $this->get($publicUrl)->assertNotFound();
            $this->actingAs($this->student)->get($studentUrl)->assertOk();
            $this->get($adminUrl)->assertForbidden();
            $this->actingAs($this->admin)->get($studentUrl)->assertForbidden();
            $this->get($adminUrl)->assertOk();
            app(PublicExample::class)->select($this->admin, $video);
            Auth::logout();
            $this->get($publicUrl)->assertOk()->assertHeader('Cache-Control', 'no-store, private');
            $this->get($studentUrl)->assertUnauthorized();
            $this->get('/videos/'.$other->id.'/'.$asset)->assertNotFound();
            $this->get(route('student.videos.asset', [$other, $asset]))->assertUnauthorized();
            app(VideoManager::class)->visibility($this->admin, $video, false);
            $this->get($publicUrl)->assertNotFound();
            $this->actingAs($this->student)->get($studentUrl)->assertNotFound();
            $this->actingAs($this->admin)->get($adminUrl)->assertOk();
            app(VideoManager::class)->visibility($this->admin, $video, true);
            app(PublicExample::class)->select($this->admin, null);
            Auth::logout();
            $this->get($publicUrl)->assertNotFound();
        }
    }

    public function test_binary_delivery_handles_ranges_head_invalid_ranges_and_exact_bytes(): void
    {
        $video = $this->upload();
        $url = route('student.videos.asset', [$video, 'media']);
        $source = file_get_contents(app(MediaFiles::class)->path($video->video_path));
        $size = strlen($source);
        $this->actingAs($this->student);
        $whole = $this->get($url)->assertOk()->assertHeader('Content-Type', 'video/mp4')->assertHeader('Content-Length', (string) $size)->assertHeader('Accept-Ranges', 'bytes');
        $this->assertSame($source, $this->bytes($whole));
        $part = $this->get($url, ['Range' => 'bytes=10-29'])->assertStatus(206)->assertHeader('Content-Range', 'bytes 10-29/'.$size)->assertHeader('Content-Length', '20');
        $this->assertSame(substr($source, 10, 20), $this->bytes($part));
        $suffix = $this->get($url, ['Range' => 'bytes=-20'])->assertStatus(206);
        $this->assertSame(substr($source, -20), $this->bytes($suffix));
        $tail = $this->get($url, ['Range' => 'bytes=10-'])->assertStatus(206);
        $this->assertSame(substr($source, 10), $this->bytes($tail));
        $this->get($url, ['Range' => 'bytes='.$size.'-'])->assertStatus(416)->assertHeader('Content-Range', 'bytes */'.$size)->assertContent('');
        $this->get($url, ['Range' => 'bytes=20-10'])->assertStatus(416);
        $this->get($url, ['Range' => 'bytes=-0'])->assertStatus(416);
        $this->get($url, ['Range' => 'bytes=garbage'])->assertOk();
        $this->get($url, ['Range' => 'bytes=0-1,4-5'])->assertOk();
        $this->get($url, ['Range' => 'bytes=-999999999'])->assertOk();
        $this->get($url, ['Range' => 'bytes=0-9', 'If-Range' => '"stale"'])->assertOk();
        $head = $this->head($url)->assertOk()->assertHeader('Content-Length', (string) $size);
        $this->assertSame('', $this->bytes($head));
        $this->assertNull($whole->headers->get('X-Sendfile'));
        $this->assertNull($whole->headers->get('X-Accel-Redirect'));
    }

    public function test_hide_and_delete_during_processing_cannot_publish_or_resurrect_content(): void
    {
        $real = app(MediaInspector::class);
        $inspector = \Mockery::mock(MediaInspector::class)->makePartial();
        $inspector->shouldReceive('inspect')->once()->andReturnUsing(function ($path) use ($real) {
            $video = LibraryVideo::firstOrFail();
            app(VideoManager::class)->visibility($this->admin, $video, false);

            return $real->inspect($path);
        });
        $this->app->instance(MediaInspector::class, $inspector);
        $hidden = $this->upload();
        $this->assertSame('ready', $hidden->processing_state);
        $this->assertFalse($hidden->is_visible);
        $this->assertFalse($hidden->isEligible());
        $inspector = \Mockery::mock(MediaInspector::class)->makePartial();
        $inspector->shouldReceive('inspect')->once()->andReturnUsing(function ($path) use ($real, $hidden) {
            $metadata = $real->inspect($path);
            app(VideoManager::class)->delete($this->admin, $hidden, $hidden->title);

            return $metadata;
        });
        $this->app->instance(MediaInspector::class, $inspector);
        $key = (string) Str::uuid();
        try {
            app(VideoManager::class)->replace($this->admin, $hidden, $this->fixture('delete-race'), null, false, $key);
            $this->fail('Deleted video resurrected.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertSame(409, $e->getStatusCode());
        }
        $this->assertNull($hidden->fresh());
        app(MediaFiles::class)->cleanup();
        $this->assertSame([], app(MediaFiles::class)->disk()->allFiles('objects'));
        $this->assertSame('canceled', DB::table('video_media_operations')->where('request_key', $key)->value('state'));
    }

    public function test_cover_validation_space_checks_permissions_and_delete_confirmation(): void
    {
        $video = $this->upload();
        $bad = $this->root.'/fixtures/unsafe.svg';
        file_put_contents($bad, '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
        $this->actingAs($this->admin)->postJson(route('admin.videos.replace', $video), ['request_key' => (string) Str::uuid(), 'cover' => new UploadedFile($bad, 'unsafe.svg', 'image/svg+xml', null, true)])->assertUnprocessable()->assertJsonValidationErrors('cover');
        $this->actingAs($this->student)->postJson(route('admin.videos.store'), [])->assertForbidden();
        $this->patchJson(route('admin.videos.visibility', $video), ['is_visible' => false])->assertForbidden();
        $this->deleteJson(route('admin.videos.destroy', $video), ['confirmation' => $video->title])->assertForbidden();
        $this->actingAs($this->admin)->deleteJson(route('admin.videos.destroy', $video), ['confirmation' => 'wrong'])->assertUnprocessable();
        $this->assertNotNull($video->fresh());
        app(PublicExample::class)->select($this->admin, $video);
        $this->deleteJson(route('admin.videos.destroy', $video), ['confirmation' => $video->title])->assertNoContent();
        $this->assertNull(VideoLibrarySetting::findOrFail(1)->public_example_video_id);
        app(MediaFiles::class)->cleanup();
        $this->assertSame([], app(MediaFiles::class)->disk()->allFiles('objects'));
        config(['video_library.minimum_free_bytes' => PHP_INT_MAX]);
        $this->postJson(route('admin.videos.store'), $this->metadata() + ['request_key' => (string) Str::uuid(), 'video' => $this->fixture()])->assertUnprocessable()->assertJsonValidationErrors('video');
    }

    public function test_path_escape_is_rejected_and_cleanup_failure_is_retained_then_retried(): void
    {
        $video = $this->upload();
        $outside = $this->root.'/fixtures/outside.mp4';
        file_put_contents($outside, 'private outside sentinel');
        $video->forceFill(['video_path' => '../fixtures/outside.mp4'])->save();
        $this->actingAs($this->student)->get(route('student.videos.asset', [$video, 'media']))->assertNotFound();
        $files = app(MediaFiles::class);
        $path = 'objects/'.Str::uuid().'/video.mp4';
        $files->reserve($path);
        $files->disk()->put($path, 'orphan');
        $files->discardLater($path);
        $disk = $files->disk();
        $failing = \Mockery::mock(MediaFiles::class)->makePartial();
        $badDisk = \Mockery::mock($disk)->makePartial();
        $badDisk->shouldReceive('delete')->with($path)->andThrow(new \RuntimeException('Injected disk failure'));
        $failing->shouldReceive('disk')->andReturn($badDisk);
        Log::spy();
        $this->assertSame(1, $failing->cleanup());
        Log::shouldHaveReceived('warning')->once();
        $this->assertSame(1, DB::table('video_asset_cleanups')->where('path', $path)->value('attempts'));
        DB::table('video_asset_cleanups')->where('path', $path)->update(['available_after' => now()->subMinute()]);
        $this->assertSame(0, $files->cleanup());
        $this->assertFileDoesNotExist($files->path($path, false));
        $this->assertSame('private outside sentinel', file_get_contents($outside));
    }

    public function test_interrupted_operations_are_recovered_without_removing_live_media(): void
    {
        $video = $this->upload();
        $token = (string) Str::uuid();
        $key = (string) Str::uuid();
        $video->forceFill(['processing_token' => $token, 'processing_started_at' => now()->subHours(2)])->save();
        DB::table('video_media_operations')->insert(['request_key' => $key, 'video_id' => $video->id, 'admin_id' => $this->admin->id,
            'kind' => 'replace', 'state' => 'processing', 'token' => $token, 'created_at' => now()->subHours(2), 'updated_at' => now()->subHours(2)]);
        $orphan = 'objects/'.$token.'/cover.jpg';
        $files = app(MediaFiles::class);
        $files->reserve($orphan);
        $files->disk()->put($orphan, 'interrupted output');
        DB::table('video_asset_cleanups')->update(['available_after' => now()->subMinute()]);
        $this->artisan('video-library:cleanup')->assertExitCode(0);
        $current = $video->fresh();
        $this->assertTrue($current->isEligible());
        $this->assertNull($current->processing_token);
        $this->assertNotNull($current->processing_error);
        $this->assertSame('failed', DB::table('video_media_operations')->where('request_key', $key)->value('state'));
        $this->assertFileExists($files->path($current->video_path));
        $this->assertFileDoesNotExist($files->path($orphan, false));
    }

    public function test_processing_replay_returns_status_and_other_media_requests_conflict(): void
    {
        $key = (string) Str::uuid();
        $real = app(MediaInspector::class);
        $inspector = \Mockery::mock(MediaInspector::class)->makePartial();
        $inspector->shouldReceive('inspect')->once()->andReturnUsing(function ($path) use ($key, $real) {
            $this->actingAs($this->admin)->getJson(route('admin.videos.operation', $key))->assertStatus(202)->assertJsonPath('processing', true)->assertJsonPath('video.processing_state', 'processing');
            $replay = $this->upload($key);
            $this->assertNotNull($replay->processing_token);
            $this->assertSame(1, LibraryVideo::count());
            try {
                app(VideoManager::class)->replace($this->admin, $replay, $this->fixture(), null, false, (string) Str::uuid());
                $this->fail('Concurrent replacement was accepted.');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                $this->assertSame(409, $e->getStatusCode());
            }

            return $real->inspect($path);
        });
        $this->app->instance(MediaInspector::class, $inspector);
        $video = $this->upload($key);
        $this->actingAs($this->admin)->getJson(route('admin.videos.operation', $key))->assertOk()->assertJsonPath('processing', false)->assertJsonPath('video.id', $video->id);
        $this->actingAs($this->student)->getJson(route('admin.videos.operation', $key))->assertForbidden();
    }

    public function test_metadata_category_limits_invalid_images_and_missing_assets(): void
    {
        $video = $this->upload();
        $published = $video->published_at;
        $manager = app(VideoManager::class);
        $data = ['title' => 'عنوان محرر', 'arabic_skill_bank_id' => $this->bank->id];
        $updated = $manager->updateMetadata($this->admin, $video, $data);
        $this->assertSame('arabic_skills', $updated->program);
        $this->assertNull($updated->qudrat_category_id);
        $this->assertTrue($published->equalTo($updated->published_at));
        $this->bank->update(['is_active' => false]);
        // Keeping an existing inactive classification is allowed; assigning it anew is rejected.
        $manager->updateMetadata($this->admin, $updated, $data);
        $this->actingAs($this->admin)->postJson(route('admin.videos.store'), $data + ['request_key' => (string) Str::uuid(), 'video' => $this->fixture()])->assertUnprocessable()->assertJsonValidationErrors('arabic_skill_bank_id');
        $this->patchJson(route('admin.videos.update', $video), $this->metadata() + ['program' => 'arabic_skills'])->assertUnprocessable()->assertJsonValidationErrors('program');
        config(['video_library.max_video_kib' => 1]);
        $this->postJson(route('admin.videos.store'), $this->metadata() + ['request_key' => (string) Str::uuid(), 'video' => $this->fixture()])->assertUnprocessable()->assertJsonValidationErrors('video');
        config(['video_library.max_video_kib' => 256000, 'video_library.max_cover_dimension' => 100]);
        $this->postJson(route('admin.videos.replace', $video), ['request_key' => (string) Str::uuid(), 'cover' => $this->cover()])->assertUnprocessable()->assertJsonValidationErrors('cover');
        config(['video_library.max_cover_dimension' => 4096]);
        $manager->updateMetadata($this->admin, $video, $this->metadata());
        app(MediaFiles::class)->disk()->delete($video->video_path);
        $this->actingAs($this->student)->get(route('student.videos.asset', [$video, 'media']))->assertNotFound();
    }

    public function test_processing_failure_keeps_old_assets_and_can_be_retried(): void
    {
        $video = $this->upload();
        $key = (string) Str::uuid();
        $real = app(MediaInspector::class);
        $failing = \Mockery::mock(MediaInspector::class)->makePartial();
        $failing->shouldReceive('poster')->once()->andThrow(ValidationException::withMessages(['video' => 'تعذّرت معالجة الغلاف ضمن الوقت المتاح.']));
        $this->app->instance(MediaInspector::class, $failing);
        $this->actingAs($this->admin)->postJson(route('admin.videos.replace', $video), ['request_key' => $key, 'video' => $this->fixture('processing-failure')])->assertUnprocessable();
        $this->assertSame($video->video_path, $video->fresh()->video_path);
        $this->assertTrue($video->fresh()->isEligible());
        $this->app->instance(MediaInspector::class, $real);
        $new = app(VideoManager::class)->replace($this->admin, $video, $this->fixture('processing-retry'), null, false, $key);
        $this->assertNotSame($video->video_path, $new->video_path);
        $replayed = app(VideoManager::class)->replace($this->admin, $new, $this->fixture(), null, false, $key);
        $this->assertSame($new->video_path, $replayed->video_path);
        $this->assertSame(1, LibraryVideo::count());
    }

    public function test_guest_management_is_denied_and_mutations_keep_web_csrf_protection(): void
    {
        $video = $this->upload();
        $this->getJson(route('admin.videos.show', $video))->assertUnauthorized();
        $this->postJson(route('admin.videos.store'), [])->assertUnauthorized();
        $this->patchJson(route('admin.videos.update', $video), [])->assertUnauthorized();
        $this->putJson(route('admin.videos.public-example'), ['video_id' => $video->id])->assertUnauthorized();
        $this->actingAs($this->admin);
        // Laravel skips CSRF in tests by default; explicitly exercise the real check.
        $csrf = new class($this->app, $this->app['encrypter']) extends \App\Http\Middleware\VerifyCsrfToken {
            protected function runningUnitTests() { return false; }
        };
        $this->app->instance(\App\Http\Middleware\VerifyCsrfToken::class, $csrf);
        $this->patchJson(route('admin.videos.visibility', $video), ['is_visible' => false])->assertStatus(419);
        $this->assertTrue($video->fresh()->is_visible);
        $this->withSession(['_token' => 'local-video-csrf'])->patchJson(route('admin.videos.visibility', $video), ['is_visible' => false, '_token' => 'local-video-csrf'])->assertOk();
        $this->assertFalse($video->fresh()->is_visible);
    }

    public function test_real_concurrent_duplicate_uploads_create_one_record_and_one_asset_pair(): void
    {
        $this->fixture();
        $key = (string) Str::uuid();
        $this->assertSame('yarub_qudrat_test_local', DB::connection()->getDatabaseName());
        DB::commit();
        $workers = [];
        try {
            for ($i = 0; $i < 2; $i++) {
                $worker = new Process([PHP_BINARY, '-c', php_ini_loaded_file(), base_path('tests/Support/video-library-concurrent-worker.php'),
                    $this->root, (string) $this->admin->id, (string) $this->category->id, $key], base_path());
                $worker->setTimeout(40)->start();
                $workers[] = $worker;
            }
            $deadline = microtime(true) + 10;
            while (count(glob($this->root.'/ready-*')) < 2 && microtime(true) < $deadline) {
                usleep(10000);
            }
            $this->assertCount(2, glob($this->root.'/ready-*'));
            file_put_contents($this->root.'/go', 'go');
            foreach ($workers as $worker) {
                $worker->wait();
                $this->assertTrue($worker->isSuccessful(), $worker->getErrorOutput());
            }
            $this->assertSame(trim($workers[0]->getOutput()), trim($workers[1]->getOutput()));
            $video = LibraryVideo::where('uploaded_by', $this->admin->id)->sole();
            $this->assertSame('ready', $video->processing_state);
            $this->assertCount(2, app(MediaFiles::class)->disk()->allFiles('objects'));
            $this->assertSame(1, DB::table('video_media_operations')->where('request_key', $key)->count());
        } finally {
            foreach ($workers as $worker) {
                if ($worker->isRunning()) {
                    $worker->stop();
                }
            }
            foreach (LibraryVideo::where('uploaded_by', $this->admin->id)->get() as $video) {
                app(VideoManager::class)->delete($this->admin, $video, $video->title);
            }
            foreach (app(MediaFiles::class)->disk()->allFiles('objects') as $path) {
                app(MediaFiles::class)->discardLater($path);
            }
            app(MediaFiles::class)->cleanup();
            DB::table('video_media_operations')->where('admin_id', $this->admin->id)->delete();
            DB::table('qudrat_categories')->where('id', $this->category->id)->delete();
            DB::table('arabic_skill_banks')->where('id', $this->bank->id)->delete();
            DB::table('users')->whereIn('id', [$this->admin->id, $this->student->id])->delete();
            DB::beginTransaction();
        }
    }

    public function test_public_example_switch_replacement_and_category_revocation_apply_to_new_requests(): void
    {
        $video = $this->upload();
        $other = $this->upload();
        $url = route('videos.example.asset', 'media');
        $this->actingAs($this->admin)->putJson(route('admin.videos.public-example'), ['video_id' => $video->id])->assertNoContent();
        Auth::logout();
        $first = $this->get($url.'?video='.$other->id)->assertOk();
        $this->assertSame(file_get_contents(app(MediaFiles::class)->path($video->video_path)), $this->bytes($first));
        $replacement = app(VideoManager::class)->replace($this->admin, $video, $this->fixture('different', ['-vf', 'hue=h=90']), null, false, (string) Str::uuid());
        $new = $this->get($url)->assertOk();
        $this->assertSame(file_get_contents(app(MediaFiles::class)->path($replacement->video_path)), $this->bytes($new));
        $replacement->forceFill(['processing_state' => 'failed'])->save();
        $this->get($url)->assertNotFound();
        $this->actingAs($this->student)->get(route('student.videos.asset', [$video, 'cover']))->assertNotFound();
        $replacement->forceFill(['processing_state' => 'ready'])->save();
        $this->category->update(['is_published' => false]);
        Auth::logout();
        $this->get($url)->assertNotFound();
        $this->category->update(['is_published' => true]);
        $this->actingAs($this->admin)->putJson(route('admin.videos.public-example'), ['video_id' => $other->id])->assertNoContent();
        $this->putJson(route('admin.videos.public-example'), ['video_id' => null])->assertNoContent();
        Auth::logout();
        $this->get($url)->assertNotFound();
    }
}
