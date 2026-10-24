<?php

namespace Tests\Feature\ArabicSkills;

use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillModel;
use App\Models\ArabicSkillQuestion;
use App\Models\ArabicSkillSession;
use App\Models\User;
use App\Services\ArabicSkills\ContentImporter;
use App\Services\ArabicSkills\ContentValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\ArabicSkillsTestCase;

class ImportTest extends ArabicSkillsTestCase
{
    private function content(): array
    {
        return app(ContentValidator::class)->validateDirectory(base_path('arabic_skills_content'));
    }

    private function import(?array $content = null, array $overwrite = []): array
    {
        return app(ContentImporter::class)->import($content ?? $this->content(), $overwrite);
    }

    public function test_import_is_immediately_active_exact_and_idempotent(): void
    {
        $report = $this->import();
        $this->assertSame(1500, $report['counts']['questions']['created']);
        $this->assertCount(50, $report['models']);
        foreach ($report['models'] as $model) {
            $this->assertSame(30, $model[2]);
            $this->assertSame(60, (int) $model[3]);
        }
        $this->assertSame(5, ArabicSkillBank::count());
        $this->assertSame(50, ArabicSkillModel::count());
        $this->assertSame(1500, ArabicSkillQuestion::active()->count());
        $before = DB::table('arabic_skill_questions')->orderBy('id')->get()->toJson();
        foreach ($this->content()['banks'] as $bank) {
            foreach ($bank['questions'] as $source) {
                $q = ArabicSkillQuestion::where('source_id', $source['id'])->firstOrFail();
                foreach (['prompt', 'options', 'matching_items', 'answer', 'explanation', 'hint', 'scoring'] as $field) {
                    $this->assertEquals($source[$field], $q->$field);
                }
                $this->assertSame(1, $q->content_revision);
            }
        }
        $report = $this->import();
        $this->assertSame(1500, $report['counts']['questions']['unchanged']);
        $this->assertSame([], $report['conflicts']);
        $this->assertSame($before, DB::table('arabic_skill_questions')->orderBy('id')->get()->toJson());
    }

    public function test_admin_conflicts_archives_additions_and_snapshots_survive_imports(): void
    {
        $this->import();
        $q = ArabicSkillQuestion::where('source_id', 'nasaq_001')->firstOrFail();
        $student = User::create(['name' => 'Local test', 'email' => 'arabic-import@example.invalid', 'password' => 'local', 'role' => 'student']);
        $session = ArabicSkillSession::create(['user_id' => $student->id, 'bank_id' => $q->bank_id, 'mode' => 'practice', 'selected_question_count' => 10, 'available_points' => 20, 'started_at' => now()]);
        $item = $session->items()->create(['bank_id' => $q->bank_id, 'question_id' => $q->id, 'position' => 1, 'content_snapshot' => $q->getAttributes(), 'response' => ['option_id' => 'a'], 'earned_points' => 2, 'result' => 'correct', 'submitted_at' => now()]);
        $snapshot = $item->fresh()->content_snapshot;
        $q->update(['prompt' => 'تعديل المشرف', 'is_active' => false]);
        $added = ArabicSkillQuestion::create(array_merge($this->content()['banks'][0]['questions'][0], ['source_id' => null, 'bank_id' => $q->bank_id, 'model_id' => null, 'number' => null, 'context_group_number' => null, 'is_active' => false]));
        $this->assertTrue($added->is_active);
        $this->assertStringStartsWith('admin_', $added->source_id);
        $report = $this->import();
        $this->assertSame(1, $report['counts']['questions']['conflicting']);
        $this->assertSame('تعديل المشرف', $q->fresh()->prompt);
        $this->assertFalse($q->fresh()->is_active);
        $this->assertSame(1501, ArabicSkillQuestion::count());
        $content = $this->content();
        $content['banks'][0]['questions'][1]['explanation'] .= '\nمراجعة المصدر';
        $report = $this->import($content);
        $this->assertSame(1, $report['counts']['questions']['updated']);
        $this->assertSame(2, ArabicSkillQuestion::where('source_id', 'nasaq_002')->firstOrFail()->content_revision);
        $this->assertSame($snapshot, $item->fresh()->content_snapshot);
        $this->assertSame(2, $item->fresh()->earned_points);
        $this->import($content, ['nasaq_001']);
        $this->assertSame($content['banks'][0]['questions'][0]['prompt'], $q->fresh()->prompt);
        $this->assertFalse($q->fresh()->is_active);
        $this->assertSame($snapshot, $item->fresh()->content_snapshot);
    }

    public function test_invalid_input_and_runtime_failures_never_partially_import(): void
    {
        $content = $this->content();
        $content['banks'][4]['questions'][299]['answer'] = [];
        try {
            $this->import($content);
            $this->fail('Invalid package accepted');
        } catch (\InvalidArgumentException) {
            $this->assertSame(0, ArabicSkillBank::count());
        }
        $event = 'eloquent.creating: '.ArabicSkillQuestion::class;
        \Illuminate\Support\Facades\Event::listen($event, function ($q) {
            if ($q->source_id === 'rasm_001') {
                throw new \RuntimeException('simulated write failure');
            }
        });
        try {
            $this->import();
            $this->fail('Expected simulated error');
        } catch (\RuntimeException $e) {
            $this->assertSame('simulated write failure', $e->getMessage());
            $this->assertSame(0, ArabicSkillBank::count());
            $this->assertSame(0, ArabicSkillModel::count());
            $this->assertSame(0, ArabicSkillQuestion::count());
        } finally {
            \Illuminate\Support\Facades\Event::forget($event);
        }
    }

    public function test_omitted_imported_records_are_retained_and_reported(): void
    {
        $this->import();
        $q = ArabicSkillQuestion::firstOrFail();
        $attributes = $q->getAttributes();
        unset($attributes['id']);
        $attributes['source_id'] = 'old_source_001';
        $attributes['number'] = null;
        DB::table('arabic_skill_questions')->insert($attributes);
        $report = $this->import();
        $this->assertContains('old_source_001', $report['omissions']);
        $this->assertSame(1501, ArabicSkillQuestion::count());
    }

    public function test_private_content_and_ownership_policies(): void
    {
        $this->import();
        $q = ArabicSkillQuestion::firstOrFail();
        foreach (['answer', 'explanation', 'hint', 'scoring'] as $key) {
            $this->assertArrayNotHasKey($key, $q->toArray());
        }
        $admin = new User(['role' => 'admin']);
        $student = new User(['role' => 'student']);
        $student->id = 50;
        $this->assertTrue(Gate::forUser($admin)->allows('create', ArabicSkillQuestion::class));
        $this->assertFalse(Gate::forUser($student)->allows('update', $q));
        $this->assertFalse(Gate::forUser($admin)->allows('delete', $q));
        $session = new ArabicSkillSession(['user_id' => 50, 'status' => 'active']);
        $this->assertTrue(Gate::forUser($student)->allows('view', $session));
        $session->user_id = 51;
        $this->assertFalse(Gate::forUser($student)->allows('view', $session));
    }
}
