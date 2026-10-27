<?php

namespace Tests;

use App\Models\ArabicSkillBank;
use App\Models\QudratCategory;
use Illuminate\Support\Facades\DB;

abstract class VideoLibraryTestCase extends QudratTestCase
{
    protected QudratCategory $category;
    protected ArabicSkillBank $bank;

    protected function setUp(): void
    {
        TestCase::setUp();
        $this->assertSame('yarub_qudrat_test_local', DB::connection()->getDatabaseName());
        $this->artisan('migrate', ['--path' => [
            'database/migrations/2014_10_12_000000_create_users_table.php',
            'database/migrations/2024_08_07_135632_create_contact_pages_table.php',
            'database/migrations/2026_10_21_000001_create_qudrat_tables.php',
            'database/migrations/2026_10_21_000002_publish_qudrat_content_by_default.php',
            'database/migrations/2026_10_23_000001_create_arabic_skill_tables.php',
            'database/migrations/2026_10_26_000001_create_library_videos_tables.php',
            'database/migrations/2026_10_26_000002_add_video_media_lifecycle.php',
            'database/migrations/2026_10_27_000001_create_video_seed_imports_table.php',
        ], '--force' => true])->assertExitCode(0);
        DB::beginTransaction();
        $this->category = QudratCategory::create(['slug' => 'video-foundation', 'title' => 'نوع تجريبي', 'sort_order' => 1]);
        $this->bank = ArabicSkillBank::create(['slug' => 'video-foundation', 'title' => 'بنك تجريبي', 'subtitle' => 'محلي', 'sort_order' => 1, 'source_metadata' => []]);
    }
}
