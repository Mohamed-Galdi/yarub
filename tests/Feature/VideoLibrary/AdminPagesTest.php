<?php

namespace Tests\Feature\VideoLibrary;

use App\Models\LibraryVideo;
use App\Models\User;
use App\Models\VideoLibrarySetting;
use Tests\VideoLibraryTestCase;

class AdminPagesTest extends VideoLibraryTestCase
{
    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->admin = User::create(['name' => 'Local admin', 'email' => 'video-pages@example.invalid', 'password' => 'local', 'role' => 'admin']);
    }

    private function video(array $extra = []): LibraryVideo
    {
        $video = LibraryVideo::create(['title' => 'العلاقات اللفظية', 'description' => 'وصف قابل للبحث', 'qudrat_category_id' => $this->category->id]);
        $video->forceFill($extra)->save();

        return $video;
    }

    public function test_pages_are_admin_only_and_completed_player_has_student_navigation(): void
    {
        $video = $this->video();
        foreach (['admin.videos.index' => [], 'admin.videos.create' => [], 'admin.videos.edit' => [$video]] as $route => $args) {
            $this->get(route($route, $args))->assertRedirect(route('login'));
        }
        $student = User::create(['name' => 'Student', 'email' => 'video-pages-student@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->actingAs($student)->get(route('admin.videos.index'))->assertRedirect(route('home'));
        $this->actingAs($student)->get(route('admin.videos.create'))->assertRedirect(route('home'));
        $this->actingAs($student)->get(route('admin.videos.edit', $video))->assertRedirect(route('home'));
        $this->actingAs($this->admin)->get(route('admin.videos.index'))->assertOk()->assertSee('الفيديوهات')->assertSee(route('admin.videos.create'), false)->assertSee(route('admin.videos.index'), false);
        $this->actingAs($student)->get(route('student.videos.index'))->assertOk()->assertSee('data-student-nav-item href="'.route('student.videos.index').'"', false);
    }

    public function test_search_type_and_visibility_filters_combine_and_paginate_newest_first(): void
    {
        $matching = $this->video(['is_visible' => false]);
        $this->video()->update(['title' => 'عنوان آخر', 'description' => 'بحث مختلف']);
        $this->actingAs($this->admin)->get(route('admin.videos.index', ['q' => 'قابل للبحث', 'type' => 'q:'.$this->category->id, 'visibility' => 'hidden']))
            ->assertOk()->assertViewHas('videos', fn ($rows) => $rows->total() === 1 && $rows->first()->id === $matching->id)->assertSee('مسح الفلاتر');
        for ($i = 0; $i < 13; $i++) $this->video(['created_at' => now()->addSeconds($i)]);
        $this->get(route('admin.videos.index', ['q' => 'العلاقات']))->assertOk()->assertViewHas('videos', fn ($rows) => $rows->count() === 12 && $rows->total() === 14)->assertSee('page=2', false);
        $this->get(route('admin.videos.index', ['q' => 'لا يوجد']))->assertOk()->assertSee('لا توجد نتائج مطابقة');
    }

    public function test_list_distinguishes_failed_hidden_ready_and_inactive_selected_example(): void
    {
        $failed = $this->video(['processing_state' => 'failed', 'processing_error' => 'أعد المحاولة']);
        $hidden = $this->video(['processing_state' => 'ready', 'is_visible' => false]);
        VideoLibrarySetting::whereKey(1)->update(['public_example_video_id' => $hidden->id]);
        $this->actingAs($this->admin)->get(route('admin.videos.index'))->assertOk()->assertSee('فشلت المعالجة')->assertSee('مخفي')->assertSee('غير متاح للزوار حاليًا')->assertSee('إزالة الاختيار')->assertViewHas('eligibleIds', []);
        $this->get(route('admin.videos.edit', $failed))->assertOk()->assertSee('إعادة المحاولة')->assertSee('أعد المحاولة');
    }

    public function test_forms_escape_content_use_real_taxonomies_and_protected_previews(): void
    {
        $video = $this->video(['processing_state' => 'ready', 'video_path' => 'objects/local/video.mp4', 'cover_path' => 'objects/local/cover.jpg']);
        $video->update(['title' => '<script>bad()</script>', 'description' => '<img src=x onerror=bad()>']);
        $this->bank->update(['is_active' => false]);
        $this->actingAs($this->admin)->get(route('admin.videos.edit', $video))->assertOk()->assertSee('&lt;script&gt;bad()&lt;/script&gt;', false)->assertDontSee('<script>bad()</script>', false)
            ->assertSee('مهارات اللغة العربية')->assertSee('بنك تجريبي')->assertSee('غير متاح')->assertSee(route('admin.videos.preview', [$video, 'media']), false)->assertSee('حفظ البيانات')->assertSee('تحديث الملفات')->assertSee('Fast Start')->assertSee('data-upload-progress', false);
        $this->get(route('admin.videos.create'))->assertOk()->assertSee('رفع وتجهيز الفيديو')->assertSee('اختياري')->assertSee('q:'.$this->category->id)->assertSee('a:'.$this->bank->id)->assertSee('data-upload-timeout="450000"', false);
    }
}
