<?php

namespace Tests\Feature\VideoLibrary;

use App\Models\LibraryVideo;
use App\Models\QudratCategory;
use App\Models\User;
use Tests\VideoLibraryTestCase;

class StudentCatalogTest extends VideoLibraryTestCase
{
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->student = User::create(['name' => 'Student', 'email' => 'catalog@example.invalid', 'password' => 'local', 'role' => 'student']);
    }

    private function video(array $values = []): LibraryVideo
    {
        $video = new LibraryVideo;
        $video->forceFill($values + ['title' => 'فهم العلاقات', 'description' => 'عبارة قابلة للبحث', 'qudrat_category_id' => $this->category->id,
            'processing_state' => 'ready', 'published_at' => now(), 'duration_seconds' => 70,
            'video_path' => 'objects/private/video.mp4', 'cover_path' => 'objects/private/cover.jpg'])->save();

        return $video;
    }

    public function test_page_and_api_require_students_and_guest_catalog_never_redirects_to_html(): void
    {
        $this->video();
        $this->get(route('student.videos.index', ['q' => 'بحث']))->assertRedirect(route('login'));
        $this->get(route('student.videos.catalog'))->assertUnauthorized()->assertJsonStructure(['message'])->assertDontSee('فهم العلاقات');
        $admin = User::create(['name' => 'Admin', 'email' => 'catalog-admin@example.invalid', 'password' => 'local', 'role' => 'admin']);
        $this->actingAs($admin)->get(route('student.videos.index'))->assertRedirect(route('home'));
        $this->get(route('student.videos.catalog'))->assertForbidden();
        $this->actingAs($this->student)->get(route('student.videos.catalog'))->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->student->delete();
        $this->get(route('student.videos.catalog'))->assertForbidden();
    }

    public function test_shared_eligibility_excludes_hidden_unready_and_both_inactive_classifications(): void
    {
        $visible = $this->video(['title' => 'متاح للطالب']);
        $this->video(['title' => 'عنوان مخفي', 'is_visible' => false]);
        foreach (['pending', 'processing', 'failed'] as $state) $this->video(['title' => 'غير جاهز '.$state, 'processing_state' => $state]);
        $inactive = QudratCategory::create(['slug' => 'catalog-inactive', 'title' => 'نوع غير منشور', 'sort_order' => 2, 'is_published' => false]);
        $this->video(['title' => 'تصنيف غير منشور', 'qudrat_category_id' => $inactive->id]);
        $this->bank->update(['is_active' => false]);
        $this->video(['title' => 'بنك غير نشط', 'qudrat_category_id' => null, 'arabic_skill_bank_id' => $this->bank->id]);
        $this->actingAs($this->student)->get(route('student.videos.index'))->assertOk()
            ->assertViewHas('videos', fn ($rows) => $rows->total() === 1 && $rows->first()->id === $visible->id)
            ->assertDontSee('عنوان مخفي')->assertDontSee('بنك غير نشط')->assertDontSee('نوع غير منشور');
        $visible->forceFill(['is_visible' => false])->save();
        $empty = $this->get(route('student.videos.catalog'))->assertOk()->assertJsonPath('total', 0);
        $this->assertStringContainsString('لا توجد فيديوهات متاحة الآن', $empty->json('html'));
    }

    public function test_search_type_pagination_and_restored_urls_use_first_publication_then_id(): void
    {
        for ($i = 0; $i < 14; $i++) $this->video(['title' => 'عنوان '.$i, 'created_at' => now()->subDays($i), 'published_at' => '2026-10-01 12:00:00']);
        $older = $this->video(['published_at' => '2026-09-01 12:00:00']);
        $this->video(['qudrat_category_id' => null, 'arabic_skill_bank_id' => $this->bank->id]);
        $parameters = ['q' => '  قابلة للبحث  ', 'type' => 'q:'.$this->category->id, 'page' => 2];
        $response = $this->actingAs($this->student)->get(route('student.videos.index', $parameters))->assertOk()
            ->assertViewHas('videos', fn ($rows) => $rows->total() === 15 && $rows->count() === 3 && $rows->last()->id === $older->id)
            ->assertViewHas('state', ['q' => 'قابلة للبحث', 'type' => 'q:'.$this->category->id, 'page' => 2]);
        $canonical = $response->viewData('canonicalUrl');
        $this->get($canonical)->assertOk()->assertViewHas('state', $response->viewData('state'));
        $this->get(route('student.videos.catalog', $parameters))->assertOk()->assertJsonPath('url', $canonical)->assertJsonPath('total', 15);
        $this->get(route('student.videos.index', ['type' => 'q:'.$this->category->id]))->assertViewHas('videos', fn ($rows) => $rows->pluck('id')->all() === LibraryVideo::eligible()->where('qudrat_category_id', $this->category->id)->newestPublished()->limit(12)->pluck('id')->all());
        $this->get(route('student.videos.catalog', ['q' => 'قابلة', 'type' => 'a:'.$this->bank->id]))->assertJsonPath('total', 1);
    }

    public function test_invalid_or_obsolete_state_falls_back_and_stale_page_clamps(): void
    {
        $this->video();
        foreach ([['q' => ['bad'], 'type' => ['bad'], 'page' => ['bad']], ['type' => 'q:99999999', 'page' => -1], ['type' => 'not-a-type', 'page' => 'oops']] as $query) {
            $this->actingAs($this->student)->get(route('student.videos.catalog', $query))->assertOk()
                ->assertJsonPath('state', ['q' => '', 'type' => '', 'page' => 1])->assertJsonPath('url', route('student.videos.index'));
        }
        $this->get(route('student.videos.catalog', ['q' => str_repeat('ع', 201), 'page' => 900000]))->assertOk()->assertJsonPath('state.q', str_repeat('ع', 200))->assertJsonPath('state.page', 1);
        $this->category->update(['is_published' => false]);
        $this->get(route('student.videos.catalog', ['type' => 'q:'.$this->category->id]))->assertJsonPath('state.type', '')->assertJsonPath('total', 0);
    }

    public function test_fragments_escape_content_and_expose_only_card_metadata_and_lazy_protected_covers(): void
    {
        $video = $this->video(['title' => '<script>bad()</script>', 'description' => 'secret-description', 'duration_seconds' => 3700]);
        $response = $this->actingAs($this->student)->get(route('student.videos.catalog'))->assertOk();
        $html = $response->json('html');
        $this->assertStringContainsString('&lt;script&gt;bad()&lt;/script&gt;', $html);
        $this->assertStringContainsString('1:01:40', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('width="360" height="640"', $html);
        $this->assertStringContainsString(route('student.videos.asset', [$video, 'cover']), $html);
        foreach (['<script>', '<video', '/media', 'objects/private', 'secret-description', 'processing_state', 'video_path', 'uploaded_by'] as $private) $this->assertStringNotContainsString($private, $html);
        $page = $this->get(route('student.videos.index'))->assertOk()->assertHeader('Cache-Control', 'no-store, private')->getContent();
        $this->assertStringContainsString('data-student-nav-item href="'.route('student.videos.index').'"', $page);
        $this->assertStringNotContainsString('src="'.route('student.videos.asset', [$video, 'media']).'"', $page);
    }

    public function test_empty_library_and_combined_no_results_have_distinct_recovery_copy(): void
    {
        $this->actingAs($this->student)->get(route('student.videos.index'))->assertOk()->assertSee('لا توجد فيديوهات متاحة الآن');
        $this->video();
        $this->get(route('student.videos.index', ['q' => 'غير موجود', 'type' => 'q:'.$this->category->id]))->assertOk()->assertSee('لا توجد فيديوهات مطابقة')->assertSee('مسح الفلاتر');
        $this->get(route('student.videos.index', ['q' => "' OR 1=1 --"]))->assertViewHas('videos', fn ($rows) => $rows->total() === 0);
    }
}
