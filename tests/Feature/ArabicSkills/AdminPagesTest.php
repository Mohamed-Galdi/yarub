<?php

namespace Tests\Feature\ArabicSkills;

use App\Http\Middleware\LogVisit;
use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillQuestion;
use App\Models\ArabicSkillSession;
use App\Models\User;
use App\Services\ArabicSkills\ContentImporter;
use App\Services\ArabicSkills\ContentValidator;
use App\Services\ArabicSkills\Practice;
use App\Services\ArabicSkills\QuestionEditor;
use Tests\ArabicSkillsTestCase;

class AdminPagesTest extends ArabicSkillsTestCase
{
    private User $admin;

    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        $this->admin = User::create(['name' => 'Admin', 'email' => 'ar-admin@example.invalid', 'password' => 'local', 'role' => 'admin']);
        $this->student = User::create(['name' => 'Student', 'email' => 'ar-review@example.invalid', 'password' => 'local', 'role' => 'student']);
        app(ContentImporter::class)->import(app(ContentValidator::class)->validateDirectory(base_path('arabic_skills_content')));
    }

    private function input(ArabicSkillQuestion $question): array
    {
        return ['bank_id' => $question->bank_id, 'type' => $question->type, 'skill' => $question->skill, 'prompt' => $question->prompt,
            'hint' => $question->hint, 'explanation' => $question->explanation,
            'options' => collect($question->options)->pluck('text', 'id')->all(),
            'items' => collect($question->matching_items)->pluck('text', 'id')->all(),
            'matches' => collect($question->answer['matches'] ?? [])->pluck('option_id', 'item_id')->all(),
            'correct' => $question->answer['option_id'] ?? null, 'revision' => app(QuestionEditor::class)->revision($question)];
    }

    public function test_all_three_editors_publish_complete_unlimited_bank_additions_without_source_membership(): void
    {
        $this->actingAs($this->admin);
        foreach (['multiple_choice', 'true_false', 'matching'] as $type) {
            $source = ArabicSkillQuestion::where('type', $type)->firstOrFail();
            $this->get(route('admin.arabic-skills.questions.create', ['type' => $type]))->assertOk()->assertSee('حفظ ونشر السؤال');
            $this->get(route('admin.arabic-skills.questions.edit', $source))->assertOk();
            $this->get(route('admin.arabic-skills.questions.preview', $source))->assertOk()->assertSee(e($source->prompt), false)->assertSee(e($source->explanation), false);
            $data = $this->input($source);
            if ($type === 'matching') {
                $data['matches'] = ['item_1' => 'a', 'item_2' => 'a'];
            }
            $data += ['source_id' => 'bad', 'model_id' => $source->model_id, 'is_active' => false, 'number' => 999];
            $this->post(route('admin.arabic-skills.questions.store'), $data)->assertSessionHasNoErrors()->assertRedirect();
            $created = ArabicSkillQuestion::orderByDesc('id')->firstOrFail();
            $this->assertStringStartsWith('admin_', $created->source_id);
            $this->assertTrue($created->is_active);
            $this->assertNull($created->model_id);
            $this->assertNull($created->number);
            $this->assertNull($created->context_group_number);
            $this->assertSame($source->bank_id, $created->bank_id);
        }
        $this->assertSame(1503, ArabicSkillQuestion::count());
        $this->assertSame(30, ArabicSkillBank::first()->models()->first()->questions()->count());
    }

    public function test_invalid_content_cannot_create_drafts_and_revision_conflicts_do_not_overwrite(): void
    {
        $this->actingAs($this->admin);
        $question = ArabicSkillQuestion::firstOrFail();
        $data = $this->input($question);
        $this->postJson(route('admin.arabic-skills.questions.store'), array_replace($data, ['explanation' => '']))->assertUnprocessable();
        $this->assertSame(1500, ArabicSkillQuestion::count());
        $this->put(route('admin.arabic-skills.questions.update', $question), array_replace($data, ['prompt' => 'سؤال معدّل']))->assertSessionHasNoErrors();
        $this->assertSame($question->content_revision + 1, $question->fresh()->content_revision);
        $this->assertSame($question->import_checksum, $question->fresh()->import_checksum);
        $this->putJson(route('admin.arabic-skills.questions.update', $question), $data)->assertUnprocessable()->assertJsonValidationErrors('revision');
        $this->assertSame('سؤال معدّل', $question->fresh()->prompt);
        $this->patchJson(route('admin.arabic-skills.questions.archive', $question), ['archived' => true, 'revision' => $data['revision']])->assertUnprocessable();
        $this->assertTrue($question->fresh()->is_active);
        $other = ArabicSkillBank::where('id', '!=', $question->bank_id)->firstOrFail();
        $this->putJson(route('admin.arabic-skills.questions.update', $question), array_replace($this->input($question->fresh()), ['bank_id' => $other->id]))->assertUnprocessable()->assertJsonValidationErrors('bank_id');
    }

    public function test_search_bank_model_type_skill_status_and_safe_escaping(): void
    {
        $question = ArabicSkillQuestion::firstOrFail();
        $this->actingAs($this->admin)->get(route('admin.arabic-skills.index'))->assertOk()->assertSee('البنوك والنماذج');
        $bank = ArabicSkillBank::withCount('questions')->firstOrFail();
        $this->assertSame(app(QuestionEditor::class)->revision($bank->fresh()), app(QuestionEditor::class)->revision($bank));
        $this->get(route('admin.arabic-skills.questions.index', ['q' => $question->source_id, 'bank_id' => $question->bank_id, 'model_id' => $question->model_id, 'type' => $question->type, 'skill' => $question->skill, 'active' => 1]))->assertOk()->assertViewHas('questions', fn ($rows) => $rows->total() === 1);
        $this->get(route('admin.arabic-skills.questions.index', ['active' => 0]))->assertOk()->assertSee('لا توجد أسئلة');
        $this->get(route('admin.arabic-skills.questions.index', ['q' => '<script>alert(1)</script>']))->assertOk()->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_archive_edits_bank_model_settings_and_review_preserve_active_and_completed_snapshots(): void
    {
        $bank = ArabicSkillBank::firstOrFail();
        $model = $bank->models()->firstOrFail();
        $practice = app(Practice::class);
        $session = $practice->start($this->student, $bank, 'model', $model);
        $item = $session->items()->firstOrFail();
        $snapshot = $item->content_snapshot;
        $question = ArabicSkillQuestion::findOrFail($item->question_id);
        $this->actingAs($this->admin);
        $this->put(route('admin.arabic-skills.questions.update', $question), array_replace($this->input($question), ['prompt' => 'محتوى جديد', 'explanation' => 'شرح جديد', 'correct' => $snapshot['answer']['option_id'] === 'a' ? 'b' : 'a']))->assertSessionHasNoErrors();
        $this->patch(route('admin.arabic-skills.questions.archive', $question), ['archived' => 1, 'revision' => app(QuestionEditor::class)->revision($question->fresh())])->assertSessionHasNoErrors();
        $this->assertFalse($question->fresh()->is_active);
        $this->put(route('admin.arabic-skills.questions.update', $question), $this->input($question->fresh()))->assertSessionHasNoErrors();
        $this->assertFalse($question->fresh()->is_active);
        $editor = app(QuestionEditor::class);
        $bankData = ['title' => 'بنك معدّل', 'subtitle' => 'موضوع معدّل', 'sort_order' => 2, 'is_active' => 0, 'revision' => $editor->revision($bank)];
        $this->patch(route('admin.arabic-skills.banks.update', $bank), $bankData)->assertSessionHasNoErrors();
        $this->patchJson(route('admin.arabic-skills.banks.update', $bank), $bankData)->assertUnprocessable();
        $this->patch(route('admin.arabic-skills.models.update', $model), ['title' => 'نموذج معدّل', 'revision' => $editor->revision($model)])->assertSessionHasNoErrors();
        $this->assertSame($snapshot, $item->fresh()->content_snapshot);
        foreach ($session->items()->get() as $row) {
            $practice->answer($this->student, $session, $row->id, $row->content_snapshot['answer']);
            $practice->next($this->student, $session, $row->id);
        }
        $this->assertSame(60, $session->fresh()->earned_points);
        $this->get(route('admin.arabic-skills.sessions.show', $session))->assertOk()->assertSee(e($snapshot['prompt']), false)->assertSee(e($snapshot['explanation']), false)->assertDontSee('محتوى جديد')
            ->assertSee('name="review_'.$item->id.'[option_id]"', false);
        $this->patch(route('admin.arabic-skills.questions.archive', $question), ['archived' => 0, 'revision' => $editor->revision($question->fresh())])->assertSessionHasNoErrors();
        $this->assertTrue($question->fresh()->is_active);
        $this->assertSame($snapshot, $item->fresh()->content_snapshot);
    }

    public function test_activity_filters_and_incomplete_review_are_read_only(): void
    {
        $bank = ArabicSkillBank::firstOrFail();
        $practice = app(Practice::class);
        $session = $practice->start($this->student, $bank);
        $item = $session->items()->firstOrFail();
        $practice->answer($this->student, $session, $item->id, $item->content_snapshot['answer']);
        $this->actingAs($this->admin)->get(route('admin.arabic-skills.sessions.index', ['student' => $this->student->email, 'bank_id' => $bank->id, 'mode' => 'practice', 'status' => 'active', 'from' => now()->format('Y-m-d'), 'to' => now()->format('Y-m-d')]))->assertOk()->assertViewHas('sessions', fn ($rows) => $rows->total() === 1);
        $this->get(route('admin.arabic-skills.sessions.index', ['to' => now()->format('Y-m-d')]))->assertOk();
        $this->get(route('admin.arabic-skills.sessions.show', $session))->assertOk()->assertSee('النقاط غير النهائية')->assertSee('لم تُرسل إجابة')->assertSee(e($item->content_snapshot['explanation']), false);
        $this->assertSame('active', $session->fresh()->status);
        $this->assertSame(1, $session->items()->whereNotNull('submitted_at')->count());
        $this->get(route('admin.arabic-skills.sessions.index', ['status' => 'completed']))->assertOk()->assertSee('لا توجد تدريبات');
    }

    public function test_guests_students_and_other_roles_cannot_manage_or_review(): void
    {
        $question = ArabicSkillQuestion::firstOrFail();
        $bank = ArabicSkillBank::firstOrFail();
        $session = app(Practice::class)->start($this->student, $bank);
        $this->get(route('admin.arabic-skills.index'))->assertRedirect(route('login'));
        $this->actingAs($this->student);
        $this->get(route('admin.arabic-skills.index'))->assertRedirect(route('home'));
        // Verify the policies independently of the existing admin middleware redirect.
        $this->withoutMiddleware(\App\Http\Middleware\AdminMiddleware::class);
        foreach (['index' => [], 'questions.index' => [], 'questions.create' => [], 'questions.edit' => [$question], 'questions.preview' => [$question], 'sessions.index' => [], 'sessions.show' => [$session]] as $action => $params) {
            $this->get(route('admin.arabic-skills.'.$action, $params))->assertForbidden();
        }
        $this->post(route('admin.arabic-skills.questions.store'), $this->input($question))->assertForbidden();
        $this->put(route('admin.arabic-skills.questions.update', $question), $this->input($question))->assertForbidden();
        $this->patch(route('admin.arabic-skills.questions.archive', $question), [])->assertForbidden();
        $this->patch(route('admin.arabic-skills.banks.update', $bank), [])->assertForbidden();
        $this->patch(route('admin.arabic-skills.models.update', $bank->models()->first()), [])->assertForbidden();
        $this->actingAs($this->admin)->delete(route('admin.arabic-skills.questions.update', $question))->assertStatus(405);
        $this->assertSame(1500, ArabicSkillQuestion::count());
        $this->assertSame(1, ArabicSkillSession::count());
    }

    public function test_failed_bank_and_model_forms_reopen_preserve_inputs_and_keep_stale_revision(): void
    {
        $this->actingAs($this->admin);
        $bank = ArabicSkillBank::firstOrFail();
        $model = $bank->models()->firstOrFail();
        $editor = app(QuestionEditor::class);
        $revision = $editor->revision($bank);
        $bank->update(['title' => 'العنوان الحالي']);
        $this->from(route('admin.arabic-skills.index'))->patch(route('admin.arabic-skills.banks.update', $bank), [
            'bank_form' => $bank->id, 'revision' => $revision, 'title' => 'مدخلات لم تُحفظ',
            'subtitle' => 'موضوع للمراجعة', 'sort_order' => 3, 'is_active' => 0,
        ])->assertSessionHasErrors('revision');
        $this->get(route('admin.arabic-skills.index'))->assertOk()->assertSee('<details  open >', false)
            ->assertSee('value="مدخلات لم تُحفظ"', false)->assertSee('value="موضوع للمراجعة"', false)
            ->assertSee('value="'.$revision.'"', false)->assertSee('العنوان الحالي');
        $this->assertSame('العنوان الحالي', $bank->fresh()->title);
        $modelRevision = $editor->revision($model);
        $model->update(['title' => 'عنوان النموذج الحالي']);
        $this->from(route('admin.arabic-skills.index'))->patch(route('admin.arabic-skills.models.update', $model), [
            'model_form' => $model->id, 'revision' => $modelRevision, 'title' => 'عنوان نموذج للمراجعة',
        ])->assertSessionHasErrors('revision');
        $this->get(route('admin.arabic-skills.index'))->assertOk()->assertSee('<details  open >', false)
            ->assertSee('value="عنوان نموذج للمراجعة"', false)->assertSee('value="'.$modelRevision.'"', false)
            ->assertSee('عنوان النموذج الحالي');
        $this->assertSame('عنوان النموذج الحالي', $model->fresh()->title);
    }
}
