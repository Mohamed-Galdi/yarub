<?php

namespace Tests\Feature\VideoLibrary;

use App\Http\Middleware\LogVisit;
use App\Models\ContactPage;
use App\Models\LibraryVideo;
use App\Models\QudratCategory;
use App\Models\User;
use App\Models\VideoLibrarySetting;
use Illuminate\Support\Facades\Auth;
use Tests\VideoLibraryTestCase;

class PlayerTest extends VideoLibraryTestCase
{
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        ContactPage::firstOrCreate([], array_fill_keys(['commercial_registration_no', 'phone_number', 'email', 'address', 'whatsapp_number', 'instagram', 'tiktok', 'snapchat'], 'local-only'));
        $this->student = User::create(['name' => 'Student', 'email' => 'player-student@example.invalid', 'password' => 'local', 'role' => 'student']);
    }

    private function video(array $extra = []): LibraryVideo
    {
        $video = new LibraryVideo;
        $video->forceFill($extra + ['title' => 'عنوان خاص', 'description' => '<script>plain description</script>', 'qudrat_category_id' => $this->category->id,
            'processing_state' => 'ready', 'published_at' => now(), 'duration_seconds' => 80, 'video_path' => 'objects/secret/video.mp4', 'cover_path' => 'objects/secret/cover.jpg'])->save();

        return $video;
    }

    public function test_player_metadata_and_related_payload_are_student_only_and_currently_eligible(): void
    {
        $video = $this->video(); $url = route('student.videos.show', $video);
        $this->get($url)->assertUnauthorized()->assertDontSee('عنوان خاص');
        $admin = User::create(['name' => 'Admin', 'email' => 'player-admin@example.invalid', 'password' => 'local', 'role' => 'admin']);
        $this->actingAs($admin)->get($url)->assertForbidden();
        $response = $this->actingAs($this->student)->get($url)->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('id', $video->id)->assertJsonPath('description', '<script>plain description</script>')
            ->assertJsonPath('share_url', route('student.videos.index', ['video' => $video->id]))->assertJsonPath('related', []);
        $this->assertSame(['id', 'title', 'type', 'duration', 'cover', 'description', 'media', 'share_url', 'related'], array_keys($response->json()));
        $response->assertDontSee('objects/secret')->assertDontSee('uploaded_by')->assertDontSee('processing_token');
        $video->forceFill(['is_visible' => false])->save();
        $this->get($url)->assertNotFound()->assertDontSee('عنوان خاص');
        $video->forceFill(['is_visible' => true, 'processing_state' => 'failed'])->save();
        $this->get($url)->assertNotFound();
        $video->forceFill(['processing_state' => 'ready'])->save(); $this->category->update(['is_published' => false]);
        $this->get($url)->assertNotFound();
        $video->delete(); $this->get($url)->assertNotFound();
    }

    public function test_related_rank_same_type_first_then_same_program_bounded_and_independent_of_search(): void
    {
        $current = $this->video();
        $otherType = QudratCategory::create(['slug' => 'other-player', 'title' => 'نوع آخر', 'sort_order' => 2]);
        $other = $this->video(['title' => 'مختلف', 'qudrat_category_id' => $otherType->id, 'published_at' => now()->addDay()]);
        $same = $this->video(['published_at' => now()->subDay()]);
        $hidden = $this->video(['is_visible' => false]);
        $arabic = $this->video(['qudrat_category_id' => null, 'arabic_skill_bank_id' => $this->bank->id]);
        $this->actingAs($this->student)->get(route('student.videos.show', $current).'?q=unmatched&type=a:999')->assertOk()
            ->assertJsonPath('related.0.id', $same->id)->assertJsonPath('related.1.id', $other->id)->assertJsonCount(2, 'related');
        for ($i = 0; $i < 8; $i++) $this->video();
        $ids = $this->get(route('student.videos.show', $current))->assertJsonCount(6, 'related')->json('related.*.id');
        $this->assertCount(6, array_unique($ids));
        foreach ([$current->id, $other->id, $hidden->id, $arabic->id] as $id) $this->assertNotContains($id, $ids);
    }

    public function test_direct_urls_preserve_library_state_and_malformed_ids_reveal_no_metadata(): void
    {
        $video = $this->video(['is_visible' => false]);
        $args = ['q' => 'بحث', 'type' => 'q:'.$this->category->id, 'video' => $video->id];
        $this->get(route('student.videos.index', $args))->assertRedirect(route('login'))->assertSessionHas('url.intended', route('student.videos.index', $args));
        $this->actingAs($this->student)->get(route('student.videos.index', $args))->assertOk()->assertViewHas('canonicalUrl', route('student.videos.index', $args))->assertDontSee('عنوان خاص');
        $this->get(route('student.videos.index', ['video' => ['bad']]))->assertOk()->assertViewHas('canonicalUrl', route('student.videos.index', ['video' => 'invalid']));
    }

    public function test_public_showcase_ignores_chosen_ids_and_never_has_a_catalog_or_automatic_example(): void
    {
        $first = $this->video(['title' => 'المثال المختار']); $second = $this->video(['title' => 'عنوان مكتبة محمي']);
        $this->get(route('videos'))->assertOk()->assertDontSee('<video', false)->assertSee('سجّل الدخول لمشاهدة جميع الفيديوهات');
        VideoLibrarySetting::whereKey(1)->update(['public_example_video_id' => $first->id]);
        $page = $this->get(route('videos', ['video' => $second->id, 'q' => 'محمي', 'type' => 'q:1', 'page' => 2]))->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')->assertSee('المثال المختار')->assertDontSee('عنوان مكتبة محمي')->assertDontSee('plain description')
            ->assertDontSee('data-video-filters', false)->assertDontSee('data-player-related', false)->assertDontSee('mpf/1.14.0')->assertSee('preload="none"', false)->getContent();
        $this->assertSame(1, substr_count($page, '<video '));
        $this->assertStringContainsString(route('videos.example.asset', 'media'), $page);
        $this->assertStringNotContainsString('/videos/'.$first->id, $page);
        $first->forceFill(['is_visible' => false])->save();
        $this->get(route('videos'))->assertOk()->assertDontSee('<video', false)->assertDontSee('المثال المختار')->assertDontSee('عنوان مكتبة محمي');
        $first->forceFill(['is_visible' => true])->save(); $this->category->update(['is_published' => false]);
        $this->get(route('videos'))->assertDontSee('<video', false);
        $this->category->update(['is_published' => true]);
        VideoLibrarySetting::whereKey(1)->update(['public_example_video_id' => null]);
        $this->get(route('videos'))->assertDontSee('<video', false);
        $this->actingAs($this->student)->get(route('videos'))->assertSee('انتقل إلى مكتبة الفيديوهات');
    }

    public function test_shared_link_login_returns_to_video_and_filters_and_cart_behavior_stays_intact(): void
    {
        $video = $this->video();
        $destination = route('student.videos.index', ['q' => 'فهم', 'type' => 'q:'.$this->category->id, 'page' => 2, 'video' => $video->id]);
        $this->get($destination)->assertRedirect(route('login'));
        $intended = session('url.intended');
        $this->post(route('student.login'), ['email' => $this->student->email, 'password' => 'local'])->assertRedirect($intended)->assertSessionMissing('url.intended');
        Auth::logout();
        $this->withSession(['url.intended' => $destination])->post(route('student.login'), ['email' => $this->student->email, 'password' => 'local', 'cart' => 1])->assertRedirect(route('cart.index'));
    }

    public function test_login_never_uses_external_or_ambiguous_return_urls(): void
    {
        $base = route('student.videos.index');
        foreach (['https://evil.invalid/student-dashboard/videos?video=1', '//evil.invalid', $base.'/../courses', $base.'?redirect=https://evil.invalid', $base.'?q[]=x&video=1', $base.'?video=1#https://evil.invalid', $base.'?q=%0d%0aLocation:evil'] as $destination) {
            Auth::logout();
            $this->withSession(['url.intended' => $destination])->post(route('student.login'), ['email' => $this->student->email, 'password' => 'local'])->assertRedirect(route('student.courses.index'));
        }
        $this->assertSame($base.'?video=invalid', \App\Services\VideoLibrary\LoginDestination::from($base.'?video=not-a-number'));
    }
}
