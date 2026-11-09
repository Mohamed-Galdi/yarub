<?php

namespace Tests\Unit;

use App\Services\Admin\DashboardAnalytics;
use App\Services\Admin\Tracking\ProgramMetrics;
use Carbon\CarbonImmutable;
use Illuminate\Auth\Access\Gate;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Facade;
use PHPUnit\Framework\TestCase;

class DashboardAnalyticsTest extends TestCase
{
    private Capsule $db;
    private Gate $gate;

    protected function setUp(): void
    {
        $container = new Container;
        Container::setInstance($container);
        Facade::clearResolvedInstances();
        Facade::setFacadeApplication($container);
        $this->db = new Capsule($container);
        $this->db->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $this->db->setAsGlobal();
        $this->db->bootEloquent();
        $container->instance('db', $this->db->getDatabaseManager());
        $container->bind('db.schema', fn () => Capsule::schema());
        $this->gate = new Gate($container, fn () => (object) ['role' => 'admin']);
        $this->gate->policy(\App\Models\LibraryVideo::class, \App\Policies\LibraryVideoPolicy::class);
        $this->gate->define('view-student-tracking', fn ($user) => $user->role === 'admin');
        $container->instance(\Illuminate\Contracts\Auth\Access\Gate::class, $this->gate);
        self::schema();
    }

    // Minimal analytics contract; all data lives in a fresh in-memory connection.
    public static function schema(): void
    {
        $tables = [
            'users' => ['role', 'deleted_at', 'created_at'],
            'qudrat_categories' => ['title', 'is_published'],
            'arabic_skill_banks' => ['title', 'is_active'],
            'qudrat_practice_sessions' => ['user_id', 'category_id', 'status', 'started_at', 'completed_at'],
            'arabic_skill_sessions' => ['user_id', 'bank_id', 'status', 'started_at', 'completed_at'],
            'qudrat_session_answers' => ['user_id', 'session_id', 'question_id', 'submitted_at', 'is_correct'],
            'arabic_skill_session_questions' => ['session_id', 'question_id', 'submitted_at', 'response', 'result'],
            'test_attempts' => ['user_id', 'test_id', 'created_at'],
            'tests' => ['course_id'], 'courses' => ['title', 'is_published'],
            'student_course_sub' => ['user_id', 'course_id', 'created_at'],
            'library_videos' => ['title', 'qudrat_category_id', 'arabic_skill_bank_id', 'processing_state', 'is_visible'],
        ];
        foreach ($tables as $table => $columns) {
            Capsule::schema()->create($table, function (Blueprint $schema) use ($columns) {
                $schema->increments('id');
                foreach ($columns as $column) {
                    if (str_ends_with($column, '_id')) {
                        $schema->unsignedBigInteger($column)->nullable();
                    } else {
                        $schema->string($column)->nullable();
                    }
                }
            });
        }
        (require dirname(__DIR__, 2).'/database/migrations/2026_11_09_000001_create_video_views_table.php')->up();
    }

    private function overview(int $days = 7): array
    {
        return (new DashboardAnalytics(new ProgramMetrics))->overview($days, CarbonImmutable::parse('2026-11-09 12:00:00'));
    }

    public function test_empty_period_has_real_zeros_and_all_calendar_days(): void
    {
        $d = $this->overview();
        self::assertSame(0, $d['active']);
        self::assertCount(7, $d['trend']);
        self::assertSame('2026-11-03', $d['trend'][0]['date']);
        self::assertSame(0, $d['actions']);
        self::assertSame(0, $d['videos']);
    }

    public function test_activity_is_deduplicated_and_completion_uses_start_cohort(): void
    {
        Capsule::table('users')->insert([
            ['id' => 1, 'role' => 'student', 'deleted_at' => null],
            ['id' => 2, 'role' => 'admin', 'deleted_at' => null],
            ['id' => 3, 'role' => 'student', 'deleted_at' => '2026-11-08'],
        ]);
        Capsule::table('qudrat_categories')->insert([
            ['id' => 1, 'title' => 'Active', 'is_published' => 1],
            ['id' => 2, 'title' => 'Unused', 'is_published' => 1],
        ]);
        Capsule::table('arabic_skill_banks')->insert(['id' => 1, 'title' => 'Arabic', 'is_active' => 1]);
        Capsule::table('qudrat_practice_sessions')->insert([
            ['id' => 1, 'user_id' => 1, 'category_id' => 1, 'status' => 'completed', 'started_at' => '2026-11-04', 'completed_at' => '2026-11-05'],
            ['id' => 2, 'user_id' => 1, 'category_id' => 1, 'status' => 'completed', 'started_at' => '2026-10-25', 'completed_at' => '2026-11-05'],
            ['id' => 3, 'user_id' => 2, 'category_id' => 1, 'status' => 'active', 'started_at' => '2026-11-05', 'completed_at' => null],
            ['id' => 4, 'user_id' => 3, 'category_id' => 1, 'status' => 'active', 'started_at' => '2026-11-05', 'completed_at' => null],
        ]);
        foreach ([[1, 1, '2026-11-04 10:00:00'], [2, 1, '2026-11-05 10:00:00'], [3, 2, '2026-11-05 10:00:00'], [4, 3, '2026-11-05 10:00:00']] as [$session, $user, $time]) {
            Capsule::table('qudrat_session_answers')->insert(['user_id' => $user, 'session_id' => $session, 'question_id' => 1, 'submitted_at' => $time, 'is_correct' => 1]);
        }
        Capsule::table('arabic_skill_sessions')->insert(['id' => 1, 'user_id' => 1, 'bank_id' => 1, 'status' => 'active', 'started_at' => '2026-11-04']);
        Capsule::table('arabic_skill_session_questions')->insert(['session_id' => 1, 'question_id' => 1, 'submitted_at' => '2026-11-04 11:00:00', 'result' => 'skipped']);
        Capsule::table('courses')->insert(['id' => 1, 'title' => 'Course', 'is_published' => 1]);
        Capsule::table('tests')->insert(['id' => 1, 'course_id' => 1]);
        Capsule::table('test_attempts')->insert(['test_id' => 1, 'user_id' => 1, 'created_at' => '2026-11-04 12:00:00']);
        Capsule::table('student_course_sub')->insert([
            ['user_id' => 1, 'course_id' => 1, 'created_at' => '2026-11-04'],
            ['user_id' => 1, 'course_id' => 1, 'created_at' => '2026-11-05'],
        ]);
        $d = $this->overview();
        self::assertSame(1, $d['active']);
        self::assertSame(1, $d['repeatStudents']);
        self::assertSame(4, $d['actions']);
        self::assertSame(2, $d['programs']['qudrat']['completed']);
        self::assertSame(1, $d['programs']['qudrat']['started']);
        self::assertSame(1, (int) $d['programs']['qudrat']['rows'][0]->finished);
        self::assertSame(1, $d['programs']['qudrat']['unused']);
        self::assertSame(1, (int) $d['courses'][0]->students);
        self::assertSame(1, (int) $d['courses'][0]->learners);
    }

    public function test_previous_period_boundaries_and_future_events_are_excluded(): void
    {
        Capsule::table('users')->insert(['id' => 1, 'role' => 'student']);
        foreach (['2026-10-27 00:00:00', '2026-11-02 12:00:01', '2026-11-09 12:00:01'] as $time) {
            Capsule::table('test_attempts')->insert(['user_id' => 1, 'created_at' => $time]);
        }
        $d = $this->overview();
        self::assertSame(1, $d['previousActive']);
        self::assertSame(0, $d['active']);
    }

    public function test_non_admin_cannot_read_analytics(): void
    {
        $this->gate->define('view-student-tracking', fn () => false);
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        $this->overview();
    }

    public function test_video_coverage_counts_only_eligible_content_and_eager_loads_categories(): void
    {
        Capsule::table('qudrat_categories')->insert([
            ['id' => 1, 'title' => 'Published', 'is_published' => 1],
            ['id' => 2, 'title' => 'Hidden', 'is_published' => 0],
        ]);
        foreach ([[1, 'ready', 1], [1, 'failed', 1], [1, 'ready', 0], [2, 'ready', 1]] as [$category, $state, $visible]) {
            Capsule::table('library_videos')->insert(['qudrat_category_id' => $category, 'processing_state' => $state, 'is_visible' => $visible]);
        }
        \Illuminate\Database\Eloquent\Model::preventLazyLoading();
        $d = $this->overview();
        self::assertSame(1, $d['videos']);
        self::assertCount(1, $d['videoCategories']);
        self::assertSame('Published', $d['videoCategories'][0]->qudratCategory->title);
        self::assertSame(1, (int) $d['videoCategories'][0]->total);
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(false);
    }

    public function test_supported_periods_have_zero_filled_days_without_per_day_queries(): void
    {
        $this->db->getConnection()->enableQueryLog();
        $short = $this->overview(7);
        $shortCount = count($this->db->getConnection()->getQueryLog());
        $this->db->getConnection()->flushQueryLog();
        $long = $this->overview(90);
        self::assertCount(90, $long['trend']);
        self::assertSame($shortCount, count($this->db->getConnection()->getQueryLog()));
        self::assertLessThan(40, $shortCount);
        self::assertCount(7, $short['trend']);
        self::assertCount(30, $this->overview(30)['trend']);
    }
    private function viewFixtures(): void
    {
        Capsule::table('users')->insert([['id' => 1, 'role' => 'student'], ['id' => 2, 'role' => 'student'], ['id' => 3, 'role' => 'admin']]);
        Capsule::table('qudrat_categories')->insert(['id' => 1, 'title' => 'Video category', 'is_published' => 1]);
        Capsule::table('library_videos')->insert(['id' => 1, 'title' => 'Video', 'qudrat_category_id' => 1, 'is_visible' => 1, 'processing_state' => 'ready']);
    }

    public function test_opening_retry_is_deduplicated_but_reopening_counts_again(): void
    {
        $this->viewFixtures();
        $service = new \App\Services\VideoLibrary\RecordView;
        $student = \App\Models\User::findOrFail(1);
        $video = \App\Models\LibraryVideo::findOrFail(1);
        $service->record($student, $video, '9dfcdb28-43b1-4a9b-9054-10ed320fd104');
        $service->record($student, $video, '9dfcdb28-43b1-4a9b-9054-10ed320fd104');
        $service->record($student, $video, '9dfcdb28-43b1-4a9b-9054-10ed320fd105');
        self::assertSame(2, Capsule::table('video_views')->count());
    }

    public function test_hidden_videos_cannot_record_opens(): void
    {
        $this->viewFixtures();
        Capsule::table('library_videos')->where('id', 1)->update(['is_visible' => 0]);
        $this->expectException(\Illuminate\Auth\Access\AuthorizationException::class);
        (new \App\Services\VideoLibrary\RecordView)->record(\App\Models\User::findOrFail(1), \App\Models\LibraryVideo::findOrFail(1), '9dfcdb28-43b1-4a9b-9054-10ed320fd104');
    }

    public function test_video_opens_feed_activity_and_rankings_without_duplicate_viewers(): void
    {
        $this->viewFixtures();
        foreach ([[1, 'a'], [1, 'b'], [2, 'c'], [3, 'd']] as [$user, $key]) {
            Capsule::table('video_views')->insert(['video_id' => 1, 'user_id' => $user, 'opening_id' => $key, 'viewed_at' => '2026-11-05 12:00:00']);
        }
        Capsule::table('test_attempts')->insert(['user_id' => 1, 'created_at' => '2026-11-05 12:00:00']);
        $d = $this->overview();
        self::assertSame(['opens' => 3, 'viewers' => 2], $d['videoActivity']);
        self::assertSame(2, $d['active']);
        self::assertSame(4, $d['actions']);
        self::assertSame(3, (int) $d['videoRankings'][0]->opens);
        self::assertSame(2, (int) $d['videoCategoryActivity'][0]->viewers);
    }

    public function test_admins_and_deleted_students_cannot_record_opens(): void
    {
        $this->viewFixtures();
        $video = \App\Models\LibraryVideo::findOrFail(1);
        Capsule::table('users')->where('id', 2)->update(['deleted_at' => '2026-11-08']);
        foreach ([3, 2] as $id) {
            try {
                (new \App\Services\VideoLibrary\RecordView)->record(\App\Models\User::withTrashed()->findOrFail($id), $video, '9dfcdb28-43b1-4a9b-9054-10ed320fd104');
                self::fail('Unauthorized account recorded a view.');
            } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                self::assertTrue(true);
            }
        }
        self::assertSame(0, Capsule::table('video_views')->count());
    }

}
