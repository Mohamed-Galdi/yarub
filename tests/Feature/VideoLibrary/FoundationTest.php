<?php

namespace Tests\Feature\VideoLibrary;

use App\Models\LibraryVideo;
use App\Models\User;
use App\Models\VideoLibrarySetting;
use App\Services\VideoLibrary\PublicExample;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tests\VideoLibraryTestCase;

class FoundationTest extends VideoLibraryTestCase
{
    private function video(bool $arabic = false): LibraryVideo
    {
        return LibraryVideo::create(['title' => 'شرح عربي', $arabic ? 'arabic_skill_bank_id' : 'qudrat_category_id' => $arabic ? $this->bank->id : $this->category->id]);
    }

    private function ready(LibraryVideo $video): LibraryVideo
    {
        $video->forceFill(['processing_state' => 'ready', 'video_path' => 'test/video.mp4', 'cover_path' => 'test/cover.jpg', 'published_at' => now()])->save();

        return $video;
    }

    private function user(string $role): User
    {
        return User::create(['name' => 'Local', 'email' => $role.'@video.example.invalid', 'password' => 'local', 'role' => $role]);
    }

    public function test_category_validation_and_derived_program_use_existing_records(): void
    {
        $qudrat = $this->video();
        $arabic = $this->video(true);
        $this->assertSame('qudrat', $qudrat->program);
        $this->assertSame('arabic_skills', $arabic->program);
        $this->assertTrue($qudrat->qudratCategory()->firstOrFail()->is($this->category));
        $this->assertTrue($arabic->arabicSkillBank()->firstOrFail()->is($this->bank));
        $this->assertSame(1, $this->category->libraryVideos()->count());
        $this->assertSame(1, $this->bank->libraryVideos()->count());
        foreach ([[], ['qudrat_category_id' => $this->category->id, 'arabic_skill_bank_id' => $this->bank->id], ['qudrat_category_id' => 999999999], ['qudrat_category_id' => $this->category->id, 'program' => 'arabic_skills']] as $invalid) {
            $this->assertTrue(Validator::make(['title' => 'اختبار'] + $invalid, LibraryVideo::metadataRules())->fails());
        }
        $this->expectException(ValidationException::class);
        LibraryVideo::create(['title' => 'بلا تصنيف']);
    }

    public function test_database_enforces_exactly_one_foreign_type_and_singleton(): void
    {
        foreach ([
            fn () => DB::table('library_videos')->insert(['title' => 'none']),
            fn () => DB::table('library_videos')->insert(['title' => 'both', 'qudrat_category_id' => $this->category->id, 'arabic_skill_bank_id' => $this->bank->id]),
            fn () => DB::table('library_videos')->insert(['title' => 'missing', 'qudrat_category_id' => 999999999]),
            fn () => DB::table('video_library_settings')->insert(['id' => 2]),
        ] as $mutation) {
            try {
                $mutation();
                $this->fail('Invalid database write accepted.');
            } catch (QueryException $e) {
                $this->assertContains((string) $e->getCode(), ['HY000', '23000']);
            }
        }
        $this->video();
        $this->video(true);
        foreach ([$this->category, $this->bank] as $type) {
            try {
                $type->delete();
                $this->fail('Referenced type deleted.');
            } catch (QueryException $e) {
                $this->assertSame('23000', (string) $e->getCode());
            }
        }
    }

    public function test_eligibility_is_shared_and_category_changes_revoke_access(): void
    {
        $video = $this->video();
        $this->assertFalse($video->isEligible());
        $this->ready($video);
        foreach (['pending', 'processing', 'failed'] as $state) {
            $video->forceFill(['processing_state' => $state])->save();
            $this->assertFalse($video->isEligible());
        }
        $video->forceFill(['processing_state' => 'ready', 'is_visible' => false])->save();
        $this->assertFalse($video->isEligible());
        $video->forceFill(['is_visible' => true])->save();
        $this->assertTrue($video->isEligible());
        $this->category->update(['is_published' => false]);
        $this->assertFalse($video->isEligible());
        $arabic = $this->ready($this->video(true));
        $this->assertTrue($arabic->isEligible());
        $this->bank->update(['is_active' => false]);
        $this->assertFalse($arabic->isEligible());
        $this->assertSame(0, LibraryVideo::eligible()->count());
    }

    public function test_student_management_and_public_permissions_are_separate(): void
    {
        $student = $this->user('student');
        $admin = $this->user('admin');
        $video = $this->ready($this->video());
        $other = $this->ready($this->video(true));
        $guestGate = Gate::forUser(null);
        $this->assertFalse($guestGate->allows('viewAny', LibraryVideo::class));
        $this->assertFalse($guestGate->allows('view', $video));
        $this->assertTrue(Gate::forUser($student)->allows('viewAny', LibraryVideo::class));
        $this->assertTrue(Gate::forUser($student)->allows('view', $video));
        $this->assertFalse(Gate::forUser($admin)->allows('view', $video));
        foreach (['create', 'manage'] as $ability) {
            $this->assertTrue(Gate::forUser($admin)->allows($ability, LibraryVideo::class));
            $this->assertFalse(Gate::forUser($student)->allows($ability, LibraryVideo::class));
            $this->assertFalse($guestGate->allows($ability, LibraryVideo::class));
        }
        foreach (['preview', 'update', 'delete'] as $ability) {
            $this->assertTrue(Gate::forUser($admin)->allows($ability, $video));
            $this->assertFalse(Gate::forUser($student)->allows($ability, $video));
            $this->assertFalse($guestGate->allows($ability, $video));
        }
        $this->assertNull(VideoLibrarySetting::eligiblePublicExample());
        $this->assertFalse($guestGate->allows('viewPublicExample', $video));
        $selector = app(PublicExample::class);
        $selector->select($admin, $video);
        $this->assertTrue($guestGate->allows('viewPublicExample', $video));
        $this->assertFalse($guestGate->allows('viewPublicExample', $other));
        DB::table('library_videos')->where('id', $video->id)->update(['is_visible' => false]);
        $this->assertFalse($guestGate->allows('viewPublicExample', $video));
        $this->assertFalse(Gate::forUser($student)->allows('view', $video));
        DB::table('library_videos')->where('id', $video->id)->update(['is_visible' => true]);
        $selector->select($admin, $other);
        $this->assertFalse($guestGate->allows('viewPublicExample', $video));
        $other->forceFill(['is_visible' => false])->save();
        $this->assertNull(VideoLibrarySetting::eligiblePublicExample());
        $this->assertFalse($guestGate->allows('viewPublicExample', $other));
        $this->assertFalse(Gate::forUser($student)->allows('view', $other));
        $this->assertTrue(Gate::forUser($admin)->allows('preview', $other));
        $other->forceFill(['is_visible' => true])->save();
        $this->assertTrue($guestGate->allows('viewPublicExample', $other));
        $this->bank->update(['is_active' => false]);
        $this->assertFalse($guestGate->allows('viewPublicExample', $other));
        $selector->select($admin, null);
        $this->assertNull(VideoLibrarySetting::eligiblePublicExample());
        $student->delete();
        $this->assertFalse(Gate::forUser($student)->allows('viewAny', LibraryVideo::class));
        $admin->delete();
        $this->assertFalse(Gate::forUser($admin)->allows('manage', LibraryVideo::class));
        $this->expectException(AuthorizationException::class);
        $selector->select($student, $video);
    }

    public function test_selection_rejects_unready_video_and_deletion_clears_reference(): void
    {
        $admin = $this->user('admin');
        $video = $this->video();
        try {
            app(PublicExample::class)->select($admin, $video);
            $this->fail('Unready example selected.');
        } catch (ValidationException) {
            $this->assertNull(VideoLibrarySetting::eligiblePublicExample());
        }
        app(PublicExample::class)->select($admin, $this->ready($video));
        $video->delete();
        $this->assertNull(VideoLibrarySetting::findOrFail(1)->public_example_video_id);
    }

    public function test_ordering_and_private_storage_foundation(): void
    {
        $first = $this->ready($this->video());
        $second = $this->ready($this->video());
        $second->forceFill(['published_at' => $first->published_at, 'created_at' => $first->created_at])->save();
        $this->assertSame([$second->id, $first->id], LibraryVideo::eligible()->newestPublished()->pluck('id')->all());
        $this->assertSame([$second->id, $first->id], LibraryVideo::newestUploaded()->pluck('id')->all());
        $published = $first->published_at;
        $first->forceFill(['is_visible' => false])->save();
        $first->update(['title' => 'عنوان جديد']);
        $first->forceFill(['is_visible' => true])->save();
        $this->assertTrue($published->equalTo($first->fresh()->published_at));
        $this->assertArrayNotHasKey('video_path', $first->toArray());
        $this->assertSame(storage_path('app/video-library'), config('filesystems.disks.video_library.root'));
        $this->assertArrayNotHasKey('url', config('filesystems.disks.video_library'));
        $this->assertNotContains(storage_path('app/video-library'), config('filesystems.links'));
    }
}
