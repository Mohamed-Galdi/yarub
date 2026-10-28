<?php

namespace Tests\Feature\ArabicSkills;

use App\Http\Middleware\LogVisit;
use App\Models\ArabicSkillQuestion;
use App\Models\User;
use App\Services\ArabicSkills\ContentImporter;
use App\Services\ArabicSkills\ContentValidator;
use App\Services\ArabicSkills\QuestionEditor;
use App\Services\ArabicSkills\Practice;
use Tests\ArabicSkillsTestCase;

class SupplementTest extends ArabicSkillsTestCase
{
    public function test_import_edit_render_and_repeat_preserve_all_choices_and_links(): void
    {
        $validator = app(ContentValidator::class);
        $content = $validator->withSupplement($validator->validateDirectory(base_path('arabic_skills_content')), base_path('arabic_skills_content/supplements/burhan_coffee_filtration.json'));
        $this->artisan('arabic-skills:import', ['--supplement' => 'arabic_skills_content/supplements/burhan_coffee_filtration.json', '--dry-run' => true])
            ->expectsOutput('Additional bank questions: 8, points: 18. Original models unchanged.')->assertExitCode(0);
        $importer = app(ContentImporter::class);
        $this->assertSame(1508, $importer->import($content)['counts']['questions']['created']);
        $this->assertSame(1508, $importer->import($content)['counts']['questions']['unchanged']);
        $editor = app(QuestionEditor::class);
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        $this->actingAs(User::create(['name' => 'Supplement admin', 'email' => 'supplement-admin@example.invalid', 'password' => 'local', 'role' => 'admin']));
        foreach (['01', '08'] as $number) {
            $question = ArabicSkillQuestion::where('source_id', 'supp_burhan_coffee_filtration_'.$number)->firstOrFail();
            $this->assertNull($question->model_id);
            $input = $question->only(['bank_id', 'type', 'skill', 'prompt', 'explanation', 'hint']);
            $input += ['revision' => $editor->revision($question), 'options' => array_column($question->options, 'text', 'id'),
                'items' => array_column($question->matching_items, 'text', 'id'), 'matches' => array_column($question->answer['matches'] ?? [], 'option_id', 'item_id'), 'correct' => $question->answer['option_id'] ?? null];
            $saved = $editor->save($input, $question);
            $this->assertSame($question->options, $saved->options);
            $this->assertSame($question->answer, $saved->answer);
            $this->assertSame($question->max_score, $saved->max_score);
            $this->get(route('admin.arabic-skills.questions.edit', $saved))->assertOk()->assertSee('options[d]', false);
            $this->get(route('admin.arabic-skills.questions.preview', $saved))->assertOk();
            $html = view('components.arabic-skills.question', ['question' => $saved->only(['prompt', 'type', 'options', 'matching_items'])])->render();
            $this->assertStringContainsString('value="d"', $html);
            if ($number === '08') {
                $this->assertStringContainsString('item_4', $html);
            }
        }
        $this->assertSame([], $importer->import($content)['conflicts']);
        $matching = ArabicSkillQuestion::where('source_id', 'supp_burhan_coffee_filtration_08')->firstOrFail();
        ArabicSkillQuestion::where('bank_id', $matching->bank_id)->whereNotIn('source_id', [
            ...array_column($content['banks'][4]['supplemental_questions'], 'id'), 'burhan_001', 'burhan_002',
        ])->update(['is_active' => false]);
        $student = User::create(['name' => 'Supplement student', 'email' => 'supplement-student@example.invalid', 'password' => 'local', 'role' => 'student']);
        $session = app(Practice::class)->start($student, $matching->bank()->firstOrFail(), 'practice');
        $this->assertSame(10, $session->selected_question_count);
        $this->assertSame(22, $session->available_points);
        $this->assertSame(4, $session->items()->where('question_id', $matching->id)->firstOrFail()->content_snapshot['max_score']);
    }
}
