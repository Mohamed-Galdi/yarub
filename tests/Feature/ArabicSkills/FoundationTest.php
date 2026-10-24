<?php

namespace Tests\Feature\ArabicSkills;

use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillQuestion;
use App\Models\ArabicSkillSession;
use App\Models\User;
use App\Services\ArabicSkills\ContentImporter;
use App\Services\ArabicSkills\ContentValidator;
use Illuminate\Support\Facades\DB;
use Tests\ArabicSkillsTestCase;

class FoundationTest extends ArabicSkillsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        app(ContentImporter::class)->import(app(ContentValidator::class)->validateDirectory(base_path('arabic_skills_content')));
    }

    public function test_incomplete_questions_cannot_be_saved_and_admin_additions_have_no_cap(): void
    {
        $source = ArabicSkillQuestion::firstOrFail();
        $payload = $source->only(['bank_id', 'skill', 'type', 'prompt', 'options', 'matching_items', 'answer', 'explanation', 'hint', 'max_score', 'scoring']);
        for ($i = 0; $i < 3; $i++) {
            $q = ArabicSkillQuestion::create($payload);
            $this->assertTrue($q->is_active);
            $this->assertNull($q->model_id);
        }
        $this->assertSame(303, $source->bank()->firstOrFail()->questions()->count());
        $payload['explanation'] = '';
        try {
            ArabicSkillQuestion::create($payload);
            $this->fail('Incomplete question saved');
        } catch (\InvalidArgumentException) {
            $this->assertSame(1503, ArabicSkillQuestion::count());
        }
    }

    private function makeSession(ArabicSkillQuestion $q): ArabicSkillSession
    {
        $user = User::create(['name' => 'Local', 'email' => 'foundation@example.invalid', 'password' => 'local', 'role' => 'student']);

        return ArabicSkillSession::create(['user_id' => $user->id, 'bank_id' => $q->bank_id, 'mode' => 'practice', 'selected_question_count' => 10, 'available_points' => 20, 'started_at' => now()]);
    }

    public function test_content_snapshots_and_submitted_answers_are_immutable(): void
    {
        $q = ArabicSkillQuestion::firstOrFail();
        $session = $this->makeSession($q);
        $item = $session->items()->create(['bank_id' => $q->bank_id, 'question_id' => $q->id, 'position' => 1, 'content_snapshot' => ['prompt' => $q->prompt, 'answer' => $q->answer], 'response' => ['option_id' => 'a'], 'earned_points' => 2, 'result' => 'correct', 'submitted_at' => now()]);
        $this->assertArrayNotHasKey('content_snapshot', $item->toArray());
        foreach ([['content_snapshot' => []], ['position' => 2], ['response' => ['option_id' => 'b']], ['earned_points' => 0]] as $edit) {
            try {
                $item->fresh()->update($edit);
                $this->fail('Immutable data changed');
            } catch (\LogicException $e) {
                $this->assertStringContainsString('immutable', $e->getMessage());
            }
        }
    }

    public function test_database_rejects_cross_bank_model_and_question_references_and_deletion(): void
    {
        $q = ArabicSkillQuestion::where('source_id', 'nasaq_001')->firstOrFail();
        $other = ArabicSkillBank::where('slug', 'rasm')->firstOrFail();
        $session = $this->makeSession($q);
        $foreignQuestion = $other->questions()->firstOrFail();
        foreach ([
            fn () => DB::table('arabic_skill_questions')->where('id', $q->id)->update(['bank_id' => $other->id]),
            fn () => $session->items()->create(['bank_id' => $q->bank_id, 'question_id' => $foreignQuestion->id, 'position' => 1, 'content_snapshot' => []]),
            fn () => $q->sourceModel()->firstOrFail()->delete(),
        ] as $mutation) {
            try {
                $mutation();
                $this->fail('Invalid relational change accepted');
            } catch (\Illuminate\Database\QueryException $e) {
                $this->assertSame('23000', (string) $e->getCode());
            }
        }
    }
}
