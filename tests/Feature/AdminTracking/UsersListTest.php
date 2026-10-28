<?php

namespace Tests\Feature\AdminTracking;

use App\Exports\StudentsListExport;
use App\Exports\UsersExport;
use App\Http\Middleware\LogVisit;
use App\Livewire\StudentsTable;
use App\Models\User;
use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillQuestion;
use App\Models\ArabicSkillSession;
use App\Services\ArabicSkills\ContentValidator;
use Illuminate\Support\Facades\DB;
use App\Services\Admin\Tracking\StudentTracking;
use Livewire\Livewire;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\AdminTrackingTestCase;

class UsersListTest extends AdminTrackingTestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'users-list-admin@example.invalid', 'password' => 'local', 'role' => 'admin']);
        $this->student = User::create(['name' => 'سارة محمد', 'email' => 'users-list-student@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->actingAs($this->admin);
    }

    public function test_existing_users_page_uses_compact_livewire_list_and_filtered_blocked_shortcut(): void
    {
        $this->get(route('admin.students'))->assertOk()->assertSee('المستخدمون')->assertSee('حسابات الطلاب')->assertSee('data-users-shell', false)->assertSee('لم يبدأ');
        $this->get(route('admin.students.deleted'))->assertRedirectContains('status=blocked');
        $table = Livewire::test(StudentsTable::class)->set('search', 'users-list-student')->assertSee('سارة محمد')->assertSee('users-list-student@example.invalid');
        $this->assertStringContainsString('list%5Bsearch%5D=users-list-student', $table->html());
        $table->call('gotoPage', 3)->set('participation', 'qudrat')->assertSet('paginators.page', 1)->assertSee('لا توجد نتائج مطابقة');
        $table->call('resetFilters')->assertSet('search', '')->assertSet('participation', 'all')->assertSet('status', 'active')->assertSet('sort', 'joined');
    }

    public function test_url_state_sort_and_page_size_are_restored_and_use_shared_metrics(): void
    {
        $state = ['search' => 'users-list-student', 'status' => 'all', 'participation' => 'neither', 'sort' => 'last_practice', 'direction' => 'asc', 'per_page' => 10, 'page' => 1];
        $table = Livewire::withQueryParams($state)->test(StudentsTable::class)->assertSet('search', $state['search'])->assertSet('status', 'all')->assertSet('perPage', 10);
        $this->assertEquals($state, $table->instance()->state());
        $this->assertSame(0, app(StudentTracking::class)->summary($this->student->id)['programs']['qudrat']['answered_questions']);
        $table->call('sortBy', 'joined')->assertSet('sort', 'joined')->assertSet('direction', 'desc');
        $table->call('sortBy', 'joined')->assertSet('direction', 'asc');
        $table->call('gotoPage', 2)->set('perPage', 50)->assertSet('paginators.page', 1);
    }

    public function test_block_restore_are_student_scoped_idempotent_and_preserve_valid_list_state(): void
    {
        $list = ['search' => 'سارة', 'status' => 'all', 'sort' => 'last_practice', 'direction' => 'asc', 'per_page' => 10, 'page' => 2, 'return_url' => 'https://example.invalid'];
        $block = route('admin.students.delete', $this->student->id);
        $restore = route('admin.students.restore', $this->student->id);
        $response = $this->delete($block, ['list' => $list])->assertRedirectContains('search=');
        $this->assertStringNotContainsString('example.invalid', $response->headers->get('Location'));
        $this->assertNotNull($this->student->fresh()->deleted_at);
        $this->deleteJson($block, ['list' => $list])->assertOk()->assertJson(['updated' => true]);
        $this->deleteJson($restore, ['list' => $list])->assertOk();
        $this->deleteJson($restore, ['list' => $list])->assertOk();
        $this->assertNull($this->student->fresh()->deleted_at);
        $this->deleteJson(route('admin.students.delete', $this->admin->id))->assertNotFound();
        $this->deleteJson(route('admin.students.restore', $this->admin->id))->assertNotFound();
        $this->deleteJson($block, ['list' => ['status' => 'invalid']])->assertUnprocessable();
        $this->assertNull($this->student->fresh()->deleted_at);
        $this->actingAs($this->student)->deleteJson($block)->assertRedirect(route('home'));
        $this->assertNull($this->student->fresh()->deleted_at);
    }

    public function test_filtered_export_has_all_matching_pages_text_cells_and_original_columns(): void
    {
        $this->student->update(['name' => '=2+3']);
        for ($i = 0; $i < 12; $i++) {
            User::create(['name' => 'طالب '.$i, 'email' => 'users-export-'.$i.'@example.invalid', 'password' => 'local', 'role' => 'student']);
        }
        $table = Livewire::test(StudentsTable::class)->set('search', 'users-export-')->set('perPage', 10)->call('exportToXLS')->assertFileDownloaded('لائحة المشتركين.xlsx');
        $export = new StudentsListExport(['search' => 'users-export-', 'per_page' => 10]);
        $this->assertSame(12, $export->query()->count());
        $raw = Excel::raw(new StudentsListExport(['search' => 'users-list-student']), \Maatwebsite\Excel\Excel::XLSX);
        $path = tempnam(sys_get_temp_dir(), 'users-export-');
        try {
            file_put_contents($path, $raw);
            // Read the exported file with the normal binder, as Excel would; the
            // export's literal-text binder must not rebind numeric XML on read.
            \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder);
            $book = IOFactory::load($path);
            $sheet = $book->getActiveSheet();
            $this->assertSame(['الإسم', 'البريد الإلكتروني', 'تاريخ التسجيل'], $sheet->rangeToArray('A1:C1')[0]);
            $this->assertSame('=2+3', $sheet->getCell('A2')->getValue());
            $this->assertSame('s', $sheet->getCell('A2')->getDataType());
            $this->assertTrue($sheet->getRightToLeft());
            $this->assertSame(2, $sheet->getHighestRow());
            $this->assertSame('AA', $sheet->getHighestColumn());
            $this->assertSame('D2', $sheet->getFreezePane());
            $this->assertSame('A1:AA2', $sheet->getAutoFilter()->getRange());
            $this->assertSame('فعال', $sheet->getCell('E2')->getValue());
            $this->assertSame('لم يبدأ', $sheet->getCell('L2')->getValue());
            $this->assertSame('غير متاح', $sheet->getCell('O2')->getValue());
            $this->assertEquals(0, $sheet->getCell('N2')->getValue());
            $this->assertSame('n', $sheet->getCell('N2')->getDataType());
        } finally {
            unlink($path);
        }
        $this->assertSame('فعال', (new UsersExport('2020-01-01', '2030-01-01'))->map($this->student)[4]);
        $this->student->delete();
        $this->assertSame('محظور', (new UsersExport('2020-01-01', '2030-01-01'))->map($this->student->fresh())[4]);
        $this->assertSame(0, (new StudentsListExport(['search' => 'users-list-student']))->query()->count());
        $this->assertSame(1, (new StudentsListExport(['search' => 'users-list-student', 'status' => 'blocked']))->query()->count());
    }

    public function test_export_progress_uses_unique_coverage_first_accuracy_and_all_recorded_activity_without_per_row_queries(): void
    {
        $bank = ArabicSkillBank::create(['slug' => 'export-fixture', 'title' => 'تجربة التصدير', 'subtitle' => 'اختبار', 'sort_order' => 1, 'source_metadata' => []]);
        $source = app(ContentValidator::class)->validateDirectory(base_path('arabic_skills_content'))['banks'][0]['questions'][0];
        $data = array_intersect_key($source, array_flip(['skill', 'type', 'prompt', 'options', 'matching_items', 'answer', 'explanation', 'hint', 'scoring', 'max_score']));
        $question = ArabicSkillQuestion::create($data + ['bank_id' => $bank->id]);
        ArabicSkillQuestion::create($data + ['bank_id' => $bank->id]);
        foreach (['incorrect', 'correct'] as $index => $result) {
            $date = '2026-10-'.(20 + $index).' 10:00:00';
            $session = ArabicSkillSession::create(['user_id' => $this->student->id, 'bank_id' => $bank->id, 'mode' => 'practice',
                'status' => $index ? 'completed' : 'abandoned', 'selected_question_count' => 1, 'available_points' => 2,
                'earned_points' => $index ? 2 : 0, 'started_at' => $date, 'completed_at' => $index ? $date : null]);
            $session->items()->create(['bank_id' => $bank->id, 'question_id' => $question->id, 'position' => 1, 'content_snapshot' => $data,
                'response' => ['option_id' => $index ? $data['answer']['option_id'] : 'b'], 'earned_points' => $index ? 2 : 0, 'result' => $result, 'submitted_at' => $date]);
        }
        $this->student->forceFill(['email_verified_at' => now()])->save();
        $this->student->delete();
        $export = new StudentsListExport(['search' => $this->student->email, 'status' => 'blocked']);
        $row = $export->query()->firstOrFail();
        DB::enableQueryLog();
        DB::flushQueryLog();
        try {
            $values = array_combine($export->headings(), $export->map($row));
            $this->assertSame([], DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
        $this->assertSame('محظور', $values['حالة الحساب']);
        $this->assertSame('مؤكد', $values['تأكيد البريد الإلكتروني']);
        $this->assertSame('بدأ', $values['مهارات اللغة العربية - المشاركة']);
        $this->assertSame(2, $values['مهارات اللغة العربية - الأسئلة المتاحة']);
        $this->assertSame(1, $values['مهارات اللغة العربية - الأسئلة المجاب عنها دون تكرار']);
        $this->assertSame(50.0, $values['مهارات اللغة العربية - تغطية الأسئلة (%)']);
        $this->assertSame(0.0, $values['مهارات اللغة العربية - دقة المحاولة الأولى (%)']);
        $this->assertSame(1, $values['مهارات اللغة العربية - التدريبات المكتملة']);
        $this->assertSame(2, $values['مهارات اللغة العربية - الإجابات المسلّمة مع التكرار']);
        $this->assertSame('2026-10-21 10:00:00', $values['آخر نشاط تدريبي']);
        $this->assertSame('2026-10-21 10:00:00', $values['مهارات اللغة العربية - آخر نشاط']);
        $this->assertSame('لم يبدأ', $values['القدرات - المشاركة']);
        $this->assertSame('غير متاح', $values['القدرات - دقة المحاولة الأولى (%)']);
    }

    public function test_missing_and_broken_avatar_markup_keeps_escaped_initials_and_action_confirmation_names(): void
    {
        $this->student->update(['avatar' => 'storage/missing-avatar.png', 'name' => '<طالب>']);
        Livewire::test(StudentsTable::class)->set('search', $this->student->email)->assertSeeHtml('&lt;طالب&gt;')->assertSee('x-on:error', false)->assertSee('data-student-account-form', false)->assertSee('data-name="&lt;طالب&gt;"', false)->assertDontSeeHtml('data-name="<طالب>"');
    }

    public function test_opening_student_preserves_back_state_and_never_blocks_account(): void
    {
        $state = ['search' => 'سارة', 'status' => 'all', 'participation' => 'neither', 'sort' => 'joined', 'direction' => 'asc', 'per_page' => 10, 'page' => 2];
        $url = route('admin.view_student', ['id' => $this->student->id, 'list' => $state]);
        $this->get($url)->assertOk()->assertSee('العودة إلى المستخدمين')->assertSee(e(route('admin.students', $state)), false);
        $this->assertNull($this->student->fresh()->deleted_at);
        $this->student->delete();
        $this->get($url)->assertOk();
        $this->get(route('admin.view_student', $this->admin->id))->assertNotFound();
    }

    public function test_query_error_has_a_recovery_state_and_retry_renders_students(): void
    {
        $tracking = app(StudentTracking::class);
        $this->mock(StudentTracking::class)->shouldReceive('directory')->once()->andThrow(new \Illuminate\Database\QueryException('mysql', 'SELECT test', [], new \RuntimeException('Isolated simulated failure')));
        $table = Livewire::test(StudentsTable::class)->assertSee('تعذّر تحميل الطلاب')->assertSee('إعادة المحاولة');
        $this->app->instance(StudentTracking::class, $tracking);
        $table->call('$refresh')->assertSee('سارة محمد')->assertDontSee('تعذّر تحميل الطلاب');
    }
}
