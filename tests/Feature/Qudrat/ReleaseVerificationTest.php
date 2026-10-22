<?php

namespace Tests\Feature\Qudrat;

use App\Http\Middleware\LogVisit;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\QudratCategory;
use App\Models\QudratPracticeSession;
use App\Models\User;
use App\Services\Qudrat\ContentImporter;
use App\Services\Qudrat\ContentValidator;
use App\Services\Qudrat\Practice;
use App\Services\Qudrat\Progress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\QudratTestCase;

class ReleaseVerificationTest extends QudratTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        Http::preventStrayRequests();
        Mail::fake();
    }

    private function user(string $role): User
    {
        return User::create(['name' => 'Release '.$role, 'email' => 'release-'.$role.'@example.invalid', 'password' => 'local', 'role' => $role]);
    }

    public function test_supplied_content_runs_through_each_student_player_and_import_preserves_history(): void
    {
        $content = app(ContentValidator::class)->validateDirectory(base_path('qudrat_content'));
        app(ContentImporter::class)->import($content);
        $student = $this->user('student');
        $this->actingAs($student);
        $practice = app(Practice::class);
        foreach (QudratCategory::orderBy('sort_order')->get() as $category) {
            $session = $practice->start($student, $category, 'practice')->fresh();
            $first = $session->items()->firstOrFail();
            $snapshot = $first->content_snapshot;
            $response = $this->get(route('student.qudrat.show', $session))->assertOk()
                ->assertSee($snapshot['prompt'])->assertDontSee('content_snapshot')->assertDontSee('correct_key');
            $response->assertDontSee($snapshot['explanation']);
            if ($category->slug === 'reading_comprehension') {
                $this->assertNotEmpty($snapshot['passage']);
                $response->assertSee($snapshot['passage']);
                $groupSizes = $session->items()->with('question')->get()->groupBy('question.passage_id');
                foreach ($groupSizes as $passageId => $items) {
                    $this->assertSame($category->questions()->published()->where('passage_id', $passageId)->count(), $items->count());
                }
            }
            $selection = $this->patchJson(route('student.qudrat.selection', [$session, $first->id]), ['option' => 'a'])
                ->assertOk()->assertExactJson(['saved' => true, 'selected' => 'a']);
            $this->assertStringNotContainsString($snapshot['explanation'], $selection->getContent());
            $practice->visit($student, $session, 'hint', $first->id);
            $this->get(route('student.qudrat.show', $session))->assertSee($snapshot['hint'])->assertDontSee($snapshot['explanation']);
            $practice->visit($student, $session, 'answer', $first->id, $snapshot['correct_key']);
            $this->get(route('student.qudrat.show', $session))->assertSee($snapshot['explanation']);
        }
        $this->assertSame(6, $student->qudratAnswers()->count());
        $before = $student->qudratAnswers()->orderBy('id')->get()->toArray();
        $snapshots = DB::table('qudrat_session_questions')->orderBy('id')->pluck('content_snapshot')->all();
        $report = app(ContentImporter::class)->import($content);
        $this->assertSame([], $report['conflicts']);
        $this->assertSame($before, $student->qudratAnswers()->orderBy('id')->get()->toArray());
        $this->assertSame($snapshots, DB::table('qudrat_session_questions')->orderBy('id')->pluck('content_snapshot')->all());
        foreach (app(Progress::class)->categories($student) as $entry) {
            $this->assertSame(1, $entry['attempted']);
            $this->assertSame(100, $entry['accuracy']);
        }
        Mail::assertNothingSent();
    }

    public function test_existing_dashboard_pages_and_purchase_gates_still_work(): void
    {
        // The historical schema is needed only for these real dashboard requests.
        // Leave the empty parent transaction before MySQL DDL (which implicitly commits).
        $this->assertSame('yarub_qudrat_test_local', DB::connection()->getDatabaseName());
        DB::rollBack();
        $this->artisan('migrate', ['--force' => true])->assertExitCode(0);
        DB::beginTransaction();
        $student = $this->user('student');
        $admin = $this->user('admin');
        $course = Course::create(['title' => 'Release course', 'description' => 'Local fixture', 'price' => 100]);
        $lesson = Lesson::create(['title' => 'Release lesson', 'description' => 'Local fixture', 'monthly_price' => 50, 'annual_price' => 500]);
        $this->actingAs($student);
        foreach (['student.courses.index', 'student.lessons.index', 'student.tests.index', 'student.certificates.index', 'student.account.index', 'student.qudrat.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('student.courses.show', $course))->assertRedirect(route('student.courses.index'));
        $this->get(route('student.lessons.show', $lesson))->assertRedirect(route('student.lessons.index'));
        $this->assertSame(0, $student->courses()->count());
        $this->assertSame(0, $student->lessons()->count());
        $this->actingAs($admin);
        foreach (['admin.dashboard', 'admin.courses', 'admin.lessons', 'admin.qudrat.index', 'admin.qudrat.questions.index', 'admin.qudrat.passages.index'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->assertSame(0, QudratPracticeSession::count());
        Mail::assertNothingSent();
    }

    public function test_production_import_is_still_blocked_even_with_existing_local_database_opt_in(): void
    {
        app()->instance('env', 'production');
        config(['qudrat.allow_development_import' => true, 'qudrat.existing_local_database' => 'yarub']);
        try {
            $this->artisan('qudrat:import')->assertExitCode(1);
            $this->assertSame(0, QudratCategory::count());
        } finally {
            app()->instance('env', 'testing');
        }
    }
}
