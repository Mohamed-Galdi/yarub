<?php

namespace Tests\Feature\AdminTracking;

use App\Http\Middleware\LogVisit;
use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillQuestion;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Payment;
use App\Models\QudratCategory;
use App\Models\QudratPassage;
use App\Models\Test;
use App\Models\TestAttempt;
use App\Models\User;
use App\Services\Admin\Tracking\StudentTracking;
use App\Services\ArabicSkills\ContentValidator;
use App\Services\ArabicSkills\Practice as ArabicPractice;
use App\Services\Qudrat\Practice as QudratPractice;
use App\Services\Qudrat\QuestionEditor;
use Illuminate\Support\Facades\DB;
use Tests\AdminTrackingTestCase;

class StudentDetailTest extends AdminTrackingTestCase
{
    protected array $additionalMigrations = [
        'database/migrations/2024_07_03_135047_create_courses_table.php',
        'database/migrations/2024_07_03_135206_create_lessons_table.php',
        'database/migrations/2024_07_03_135715_create_tests_table.php',
        'database/migrations/2024_07_03_140503_create_payments_table.php',
        'database/migrations/2024_07_03_140603_create_student_course_sub_table.php',
        'database/migrations/2024_07_03_140807_create_student_lesson_sub_table.php',
        'database/migrations/2024_07_25_130939_create_certificates_table.php',
        'database/migrations/2024_07_25_183523_create_test_attempts_table.php',
    ];

    private User $admin;

    private User $student;

    private Course $course;

    private Lesson $lesson;

    private Certificate $certificate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'detail-admin@example.invalid', 'password' => 'local', 'role' => 'admin']);
        $this->student = User::create(['name' => 'الطالبة للتفاصيل', 'email' => 'detail-student@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->course = Course::create(['title' => 'دورة محفوظة', 'description' => 'وصف', 'price' => 500]);
        $this->lesson = Lesson::create(['title' => 'شرح محفوظ', 'description' => 'وصف', 'monthly_price' => 150, 'annual_price' => 1000]);
        $payment = Payment::create(['user_id' => $this->student->id, 'payment_id' => 'isolated-detail-test', 'payment_status' => 'paid', 'original_amount' => '650', 'payment_amount' => '650', 'payment_message' => 'fixture']);
        $this->student->courses()->attach($this->course->id, ['payment_id' => $payment->id, 'cost' => 450, 'created_at' => '2026-08-01 10:00:00']);
        $this->student->lessons()->attach($this->lesson->id, ['payment_id' => $payment->id, 'cost' => 125, 'sub_plan' => 'monthly', 'created_at' => '2026-08-01 10:00:00']);
        $test = Test::create(['title' => 'اختبار محفوظ', 'course_id' => $this->course->id, 'type' => 'عادي']);
        TestAttempt::create(['user_id' => $this->student->id, 'test_id' => $test->id, 'answers' => [], 'score' => 75]);
        $this->certificate = Certificate::create(['user_id' => $this->student->id, 'lesson_id' => $this->lesson->id]);
        $this->actingAs($this->admin);
    }

    private function url(array $query = []): string
    {
        return route('admin.view_student', ['id' => $this->student->id] + $query);
    }

    private function qudrat(bool $reading = false): array
    {
        $category = QudratCategory::create(['slug' => $reading ? 'reading_comprehension' : 'verbal_analogies', 'title' => 'نوع التفاصيل', 'sort_order' => 1]);
        $passage = $reading ? QudratPassage::create(['category_id' => $category->id, 'external_key' => 'detail-passage', 'text' => 'سياق القراءة الأصلي', 'sort_order' => 1]) : null;
        $questions = [];
        foreach (range(1, 2) as $i) {
            $questions[] = app(QuestionEditor::class)->save(['category_id' => $category->id, 'passage_id' => $passage?->id, 'sort_order' => $i,
                'prompt' => 'سؤال تاريخي '.$i, 'hint' => 'تلميح قديم', 'explanation' => 'تفسير تاريخي', 'correct_option_key' => 'c',
                'options' => ['a' => 'اختيار خاطئ', 'b' => 'خيار ثان', 'c' => 'الإجابة الأصلية', 'd' => 'خيار رابع']]);
        }
        $practice = app(QudratPractice::class);
        $session = $practice->start($this->student, $category, 'practice');
        $practice->visit($this->student, $session, 'answer', $session->items()->first()->id, 'a');

        return [$category, $session, $questions, $passage];
    }

    public function test_overview_preserves_legacy_counts_without_loading_unselected_records(): void
    {
        $response = $this->get($this->url())->assertOk()->assertSee('نظرة عامة')->assertSee('القدرات')->assertSee('مهارات اللغة العربية')->assertSee('الشهادات')->assertSee('غير متاح')->assertDontSee('دورة محفوظة');
        $model = $response->viewData('student');
        $this->assertSame(1, $model->active_courses_count);
        $this->assertSame(1, $model->active_lessons_count);
        $this->assertSame(1, $model->test_attempts_count);
        $this->assertSame(1, $model->certificates_count);
        foreach (['activeCourses', 'activeLessons', 'test_attempts', 'certificates'] as $relation) {
            $this->assertFalse($model->relationLoaded($relation));
        }
        $this->assertNull($response->viewData('records'));
        $this->assertNull($response->viewData('history'));
        $this->assertSame(app(StudentTracking::class)->summary($this->student->id), $response->viewData('summary'));
    }

    public function test_legacy_tabs_keep_cost_plan_dates_grades_and_certificate_link(): void
    {
        $hidden = Course::create(['title' => 'دورة غير منشورة', 'description' => 'وصف', 'price' => 1, 'is_published' => false]);
        $this->student->courses()->attach($hidden->id, ['payment_id' => Payment::where('user_id', $this->student->id)->value('id'), 'cost' => 1]);
        $this->get($this->url(['tab' => 'courses']))->assertOk()->assertSee('دورة محفوظة')->assertSee('450')->assertSee('01-08-2026')->assertDontSee('دورة غير منشورة');
        $this->student->courses()->detach();
        $before = DB::table('student_lesson_sub')->where('user_id', $this->student->id)->value('created_at');
        $this->get($this->url(['tab' => 'lessons']))->assertOk()->assertSee('شرح محفوظ')->assertSee('شهري')->assertSee('125')->assertSee('01-08-2026')->assertSee('01-09-2026');
        $this->assertSame($before, DB::table('student_lesson_sub')->where('user_id', $this->student->id)->value('created_at'));
        DB::table('student_lesson_sub')->where('user_id', $this->student->id)->update(['created_at' => null]);
        $this->get($this->url(['tab' => 'lessons']))->assertOk()->assertSee('غير متاح')->assertSee('125');
        $this->get($this->url(['tab' => 'tests']))->assertOk()->assertSee('اختبار محفوظ')->assertSee('دورة محفوظة')->assertSee('75٪');
        $this->get($this->url(['tab' => 'certificates']))->assertOk()->assertSee('شرح محفوظ')->assertSee(e(route('admin.certificates.view', $this->certificate->id)), false);
    }

    public function test_program_breakdown_and_history_share_metrics_and_preserve_independent_pages(): void
    {
        [$category,$session] = $this->qudrat();
        for ($i = 0; $i < 21; $i++) {
            DB::table('qudrat_practice_sessions')->insert(['user_id' => $this->student->id, 'category_id' => $category->id, 'mode' => 'practice', 'status' => 'closed', 'current_position' => 1, 'started_at' => '2026-01-01 00:00:00']);
        }
        $query = ['tab' => 'qudrat', 'qudrat_page' => 2, 'arabic_skills_page' => 3, 'list' => ['search' => 'طالب', 'status' => 'all', 'page' => 4]];
        $response = $this->get($this->url($query))->assertOk()->assertSee('سجل التدريبات')->assertSee('نوع التفاصيل');
        $this->assertCount(2, $response->viewData('history')->items());
        $this->assertSame(22, $response->viewData('history')->total());
        $this->assertSame(1, $response->viewData('summary')['programs']['qudrat']['answered_questions']);
        $type = collect($response->viewData('types'))->firstWhere('id', $category->id);
        $this->assertSame(50.0, $type['coverage']);
        $this->assertSame(0.0, $type['first_attempt_accuracy']);
        $this->assertStringContainsString('arabic_skills_page=3', $response->viewData('history')->previousPageUrl());
        $this->assertStringContainsString('list%5Bpage%5D=4', $response->viewData('history')->previousPageUrl());
        $this->get($this->url(['tab' => 'qudrat', 'qudrat_type' => 99999999]))->assertNotFound();
        $this->getJson($this->url(['tab' => 'invalid']))->assertUnprocessable();
    }

    public function test_qudrat_html_review_preserves_snapshot_context_unanswered_state_and_back_link(): void
    {
        [$category,$session,$questions,$passage] = $this->qudrat(true);
        $before = DB::table('qudrat_practice_sessions')->where('id', $session->id)->first();
        $passage->update(['text' => 'سياق معدل حالي']);
        $questions[0]->update(['prompt' => 'سؤال معدل حالي', 'correct_option_key' => 'a']);
        $url = route('admin.students.tracking.review', ['student' => $this->student->id, 'program' => 'qudrat', 'session' => $session->id, 'qudrat_page' => 2, 'list' => ['search' => 'طالب', 'page' => 4]]);
        $response = $this->get($url)->assertOk()->assertSee('سياق القراءة الأصلي')->assertSee('سؤال تاريخي 1')->assertSee('الإجابة الأصلية')->assertSee('إجابة غير صحيحة')->assertSee('لم تُرسل إجابة')->assertSee('غير نهائية')->assertDontSee('سياق معدل حالي')->assertDontSee('سؤال معدل حالي');
        $this->assertSame('qudrat', $response->viewData('state')['tab']);
        $this->assertSame(2, $response->viewData('state')['qudrat_page']);
        $this->assertSame(4, $response->viewData('list')['page']);
        $this->assertEquals($before, DB::table('qudrat_practice_sessions')->where('id', $session->id)->first());
        $this->getJson($url)->assertOk()->assertJsonPath('items.1.result', 'unanswered');
        DB::table('qudrat_session_questions')->where('session_id', $session->id)->update(['content_snapshot' => '{}']);
        $this->get($url)->assertOk()->assertSee('نسخة السؤال التاريخية غير مكتملة')->assertDontSee('سؤال معدل حالي');
    }

    public function test_arabic_review_renders_partial_matching_from_original_snapshot_without_hint_claim(): void
    {
        $bank = ArabicSkillBank::create(['slug' => 'detail-bank', 'title' => 'بنك التفاصيل', 'subtitle' => 'وصف', 'source_metadata' => [], 'sort_order' => 1]);
        $source = collect(app(ContentValidator::class)->validateDirectory(base_path('arabic_skills_content'))['banks'][0]['questions'])->firstWhere('type', 'matching');
        for ($i = 0; $i < 10; $i++) {
            ArabicSkillQuestion::create(array_intersect_key($source, array_flip(['skill', 'type', 'prompt', 'options', 'matching_items', 'answer', 'explanation', 'hint', 'scoring', 'max_score'])) + ['bank_id' => $bank->id]);
        }
        $practice = app(ArabicPractice::class);
        $session = $practice->start($this->student, $bank);
        $item = $session->items()->first();
        $response = $item->content_snapshot['answer'];
        $right = $response['matches'][0]['option_id'];
        $response['matches'][0]['option_id'] = collect($item->content_snapshot['options'])->pluck('id')->first(fn ($id) => $id !== $right);
        $practice->answer($this->student, $session, $item->id, $response);
        $before = DB::table('arabic_skill_sessions')->where('id', $session->id)->first();
        ArabicSkillQuestion::whereKey($item->question_id)->firstOrFail()->update(['prompt' => 'نص جديد لا يمثل التاريخ']);
        $url = route('admin.students.tracking.review', [$this->student->id, 'arabic_skills', $session->id]);
        $this->get($url)->assertOk()->assertSee('إجابة صحيحة جزئيًا')->assertSee('اختيار الطالب')->assertSee('الإجابة الصحيحة')->assertSee('استخدام التلميحات غير متاح')->assertSee('لم تُرسل إجابة')->assertDontSee('نص جديد لا يمثل التاريخ');
        $this->get($this->url(['tab' => 'arabic_skills']))->assertOk()->assertSee('بنك التفاصيل')->assertSee('غير نهائية');
        $this->assertEquals($before, DB::table('arabic_skill_sessions')->where('id', $session->id)->first());
    }

    public function test_detail_actions_preserve_section_state_and_reviews_reject_other_owners_and_students(): void
    {
        [, $session] = $this->qudrat();
        $payload = ['list' => ['search' => 'طالب', 'page' => 2], 'detail' => ['tab' => 'qudrat', 'qudrat_page' => 2]];
        $response = $this->delete(route('admin.students.delete', $this->student->id), $payload)->assertRedirectContains('tab=qudrat');
        $this->assertStringContainsString('/students/view/'.$this->student->id, $response->headers->get('Location'));
        $this->get($this->url(['tab' => 'qudrat']))->assertOk()->assertSee('محظور')->assertSee('إزالة الحظر');
        $this->delete(route('admin.students.restore', $this->student->id), $payload)->assertRedirectContains('qudrat_page=2');
        $other = User::create(['name' => 'Other', 'email' => 'detail-other@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->get(route('admin.students.tracking.review', [$other->id, 'qudrat', $session->id]))->assertNotFound();
        $this->actingAs($other)->get($this->url())->assertRedirect(route('home'));
        $this->get(route('admin.students.tracking.review', [$this->student->id, 'qudrat', $session->id]))->assertRedirect(route('home'));
    }
}
