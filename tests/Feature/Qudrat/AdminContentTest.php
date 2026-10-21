<?php

namespace Tests\Feature\Qudrat;

use App\Http\Middleware\LogVisit;
use App\Livewire\QudratQuestionsTable;
use App\Models\QudratCategory;
use App\Models\QudratQuestion;
use App\Models\User;
use App\Services\Qudrat\ContentImporter;
use App\Services\Qudrat\ContentValidator;
use App\Services\Qudrat\QuestionEditor;
use Livewire\Livewire;
use Tests\QudratTestCase;

class AdminContentTest extends QudratTestCase
{
    private User $admin;

    private QudratCategory $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@example.invalid', 'password' => 'local', 'role' => 'admin']);
        $this->category = QudratCategory::create(['slug' => 'verbal_analogies', 'title' => 'التناظر اللفظي', 'sort_order' => 1]);
    }

    private function payload(array $changes = []): array
    {
        return array_merge(['category_id' => $this->category->id, 'prompt' => 'مفتاح : باب', 'hint' => 'تلميح', 'explanation' => 'تفسير الإجابة',
            'sort_order' => 1, 'correct_option_key' => 'c', 'options' => ['a' => 'اختيار أول', 'b' => 'اختيار ثان', 'c' => 'ريموت : تلفاز', 'd' => 'اختيار رابع'],
        ], $changes);
    }

    private function question(array $changes = []): QudratQuestion
    {
        return app(QuestionEditor::class)->save($this->payload($changes));
    }

    private function revision(QudratQuestion $question): string
    {
        return app(QuestionEditor::class)->revision($question);
    }

    public function test_ordinary_admin_can_browse_create_edit_and_preview(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('admin.qudrat.index'))->assertOk()->assertSee('مراجعة محتوى القدرات');
        $this->get(route('admin.qudrat.questions.create'))->assertOk();
        $this->post(route('admin.qudrat.questions.store'), $this->payload())->assertRedirect();
        $question = QudratQuestion::with('options')->firstOrFail();
        $this->assertTrue($question->is_published);
        $this->assertTrue($this->category->is_published);
        $optionIds = $question->options->pluck('id', 'local_key')->all();
        $this->get(route('admin.qudrat.questions.edit', $question))->assertOk()->assertSee('مفتاح : باب');
        $this->put(route('admin.qudrat.questions.update', $question), $this->payload(['prompt' => 'سؤال محرر', 'revision' => $this->revision($question)]))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($optionIds, $question->options()->pluck('id', 'local_key')->all());
        $this->get(route('admin.qudrat.questions.preview', $question))->assertOk()->assertSee('سؤال محرر')->assertSee('تفسير الإجابة');
    }

    public function test_guest_and_student_cannot_read_or_mutate_admin_content(): void
    {
        $question = $this->question();
        $this->get(route('admin.qudrat.questions.preview', $question))->assertRedirect(route('login'));
        $this->post(route('admin.qudrat.questions.store'), $this->payload())->assertRedirect(route('login'));
        $student = User::create(['name' => 'Student', 'email' => 'student@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->actingAs($student);
        foreach (['admin.qudrat.index' => [], 'admin.qudrat.questions.index' => [], 'admin.qudrat.questions.preview' => [$question]] as $route => $params) {
            $this->get(route($route, $params))->assertRedirect(route('home'));
        }
        $this->put(route('admin.qudrat.questions.update', $question), $this->payload(['prompt' => 'tampered']))->assertRedirect(route('home'));
        $this->assertSame('مفتاح : باب', $question->fresh()->prompt);
        $this->get('/student-dashboard/qudrat')->assertNotFound();
    }

    public function test_incomplete_questions_cannot_be_saved_as_drafts(): void
    {
        $this->actingAs($this->admin);
        $payload = $this->payload(['is_published' => true, 'explanation' => '', 'hint' => '', 'correct_option_key' => 'z']);
        unset($payload['options']['d']);
        $this->post(route('admin.qudrat.questions.store'), $payload)->assertSessionHasErrors(['options', 'options.d', 'hint', 'explanation', 'correct_option_key']);
        $this->assertSame(0, QudratQuestion::count());
        $this->post(route('admin.qudrat.questions.store'), $this->payload(['hint' => null, 'explanation' => null, 'is_published' => false]))->assertSessionHasErrors(['hint', 'explanation']);
        $this->assertSame(0, QudratQuestion::count());
        $this->post(route('admin.qudrat.questions.store'), $this->payload(['is_published' => false]))->assertSessionHasNoErrors();
        $this->assertTrue(QudratQuestion::firstOrFail()->is_published);
        $this->get(route('admin.qudrat.questions.create'))->assertDontSee('name="is_published"', false)->assertDontSee('مسودة');
    }

    public function test_publication_is_automatic_and_archive_restore_keeps_records(): void
    {
        $this->actingAs($this->admin);
        $question = $this->question();
        $this->patch(route('admin.qudrat.categories.update', $this->category), ['title' => $this->category->title, 'sort_order' => 1, 'is_published' => false])->assertSessionHasNoErrors();
        $this->assertTrue($this->category->fresh()->is_published);
        $this->assertSame(1, QudratQuestion::published()->count());
        $question = $question->fresh(['options']);
        $this->patch(route('admin.qudrat.questions.archive', $question), ['archived' => true, 'revision' => $this->revision($question)])->assertSessionHasNoErrors();
        $this->assertSame(1, QudratQuestion::count());
        $this->assertSame(4, $question->options()->count());
        $this->assertSame(0, QudratQuestion::published()->count());
        $question = $question->fresh(['options']);
        $this->put(route('admin.qudrat.questions.update', $question), $this->payload(['is_published' => true, 'revision' => $this->revision($question)]))->assertSessionHasNoErrors();
        $this->assertFalse($question->fresh()->is_published);
        $question = $question->fresh(['options']);
        $this->patch(route('admin.qudrat.questions.archive', $question), ['archived' => false, 'revision' => $this->revision($question)])->assertSessionHasNoErrors();
        $this->assertNull($question->fresh()->archived_at);
        $this->assertTrue($question->fresh()->is_published);
    }

    public function test_reading_requires_a_same_category_passage_and_shared_edits_update_previews(): void
    {
        $this->actingAs($this->admin);
        $reading = QudratCategory::create(['slug' => 'reading_comprehension', 'title' => 'استيعاب المقروء', 'sort_order' => 5]);
        $passage = $reading->passages()->create(['external_key' => 'reading_local_p001', 'text' => "فقرة أولى\n\nفقرة ثانية", 'sort_order' => 1]);
        $this->post(route('admin.qudrat.questions.store'), $this->payload(['category_id' => $reading->id]))->assertSessionHasErrors('passage_id');
        $this->post(route('admin.qudrat.questions.store'), $this->payload(['passage_id' => $passage->id]))->assertSessionHasErrors('passage_id');
        $first = $this->question(['category_id' => $reading->id, 'passage_id' => $passage->id]);
        $second = $this->question(['category_id' => $reading->id, 'passage_id' => $passage->id, 'sort_order' => 2]);
        $this->get(route('admin.qudrat.passages.index'))->assertOk()->assertSee('فقرة أولى');
        $this->get(route('admin.qudrat.passages.edit', $passage))->assertOk()->assertSee('مشترك بين 2 أسئلة');
        $this->put(route('admin.qudrat.passages.update', $passage), ['text' => "نص مراجع\n\nفقرة محفوظة", 'revision' => hash('sha256', $passage->text)])->assertSessionHasNoErrors();
        foreach ([$first, $second] as $question) {
            $this->get(route('admin.qudrat.questions.preview', $question))->assertOk()->assertSee('نص مراجع')->assertSee('فقرة محفوظة');
        }
    }

    public function test_preview_escapes_content_and_never_records_an_attempt(): void
    {
        $this->actingAs($this->admin);
        $question = $this->question(['prompt' => '<script>alert(1)</script>']);
        $this->get(route('admin.qudrat.questions.preview', $question))->assertOk()->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        $this->assertDatabaseCount('qudrat_session_answers', 0);
        $this->assertDatabaseCount('qudrat_practice_sessions', 0);
    }

    public function test_stale_question_and_passage_edits_cannot_overwrite_newer_content(): void
    {
        $this->actingAs($this->admin);
        $question = $this->question();
        $revision = $this->revision($question);
        $question->update(['prompt' => 'أحدث تعديل']);
        $this->put(route('admin.qudrat.questions.update', $question), $this->payload(['revision' => $revision]))->assertSessionHasErrors('revision');
        $this->assertSame('أحدث تعديل', $question->fresh()->prompt);
        $passage = $this->category->passages()->create(['external_key' => 'local_passage', 'text' => 'original', 'sort_order' => 1]);
        $passage->update(['text' => 'changed']);
        $this->put(route('admin.qudrat.passages.update', $passage), ['text' => 'stale', 'revision' => hash('sha256', 'original')])->assertSessionHasErrors('revision');
        $this->assertSame('changed', $passage->fresh()->text);
    }

    public function test_table_searches_option_text_filters_status_and_is_authorized_on_updates(): void
    {
        $first = $this->question();
        $second = $this->question(['prompt' => 'سؤال منشور', 'sort_order' => 2, 'is_published' => true]);
        app(QuestionEditor::class)->archive($second, true, $this->revision($second));
        $this->actingAs($this->admin)->get(route('admin.qudrat.questions.index'))->assertOk();
        Livewire::actingAs($this->admin)->test(QudratQuestionsTable::class)->assertSee('مفتاح : باب')
            ->set('search', 'ريموت')->assertSee('مفتاح : باب');
        Livewire::actingAs($this->admin)->test(QudratQuestionsTable::class)
            ->set('filters.select.is_published', 'published')->assertSee('مفتاح : باب')->assertDontSee('سؤال منشور');
        Livewire::actingAs($this->admin)->test(QudratQuestionsTable::class)
            ->set('filters.select.is_published', 'archived')->assertSee('سؤال منشور')->assertDontSee('مفتاح : باب');
        $component = Livewire::actingAs($this->admin)->test(QudratQuestionsTable::class);
        $student = User::create(['name' => 'Student', 'email' => 'livewire@example.invalid', 'password' => 'local', 'role' => 'student']);
        Livewire::actingAs($student)->test(QudratQuestionsTable::class)->assertForbidden();
        $this->actingAs($student);
        $component->call('$refresh')->assertForbidden();
    }

    public function test_imported_questions_are_published_and_editor_changes_are_preserved_by_import(): void
    {
        // Remove only this test's empty category fixture before importing the six supplied categories.
        $this->category->delete();
        $content = app(ContentValidator::class)->validateDirectory(base_path('qudrat_content'));
        app(ContentImporter::class)->import($content);
        $question = QudratQuestion::with('options')->where('external_key', 'verbal_analogies_001')->firstOrFail();
        $this->actingAs($this->admin)->put(route('admin.qudrat.questions.update', $question), $this->payload([
            'category_id' => $question->category_id, 'prompt' => 'مراجعة المشرف', 'revision' => $this->revision($question),
        ]))->assertSessionHasNoErrors();
        $report = app(ContentImporter::class)->import($content);
        $this->assertSame(1, $report['counts']['questions']['conflicting']);
        $this->assertSame('مراجعة المشرف', $question->fresh()->prompt);
        $this->get(route('admin.qudrat.index'))->assertOk()->assertSee('منشور')->assertDontSee('مسودة');
    }
}
