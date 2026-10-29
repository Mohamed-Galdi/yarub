<?php

namespace Tests\Feature\ArabicSkills;

use App\Http\Middleware\LogVisit;
use App\Models\ArabicSkillBank;
use App\Models\User;
use App\Services\ArabicSkills\ContentImporter;
use App\Services\ArabicSkills\ContentValidator;
use App\Services\ArabicSkills\Practice;
use App\Support\ArabicSkillPrompt;
use Tests\ArabicSkillsTestCase;

class SessionExperienceTest extends ArabicSkillsTestCase
{
    public function test_activity_hierarchy_feedback_privacy_binary_scores_and_partial_matching_through_a_whole_session(): void
    {
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        $validator = app(ContentValidator::class);
        app(ContentImporter::class)->import($validator->withSupplement($validator->validateDirectory(base_path('arabic_skills_content')), base_path('arabic_skills_content/supplements/burhan_coffee_filtration.json')));
        $student = User::create(['name' => 'Session UX', 'email' => 'session-ux@example.invalid', 'password' => 'local', 'role' => 'student']);
        $bank = ArabicSkillBank::where('slug', 'burhan')->firstOrFail();
        $practice = app(Practice::class);
        $session = $practice->start($student, $bank, 'practice', null, 'coffee-filtration');
        $this->actingAs($student);
        foreach ($session->items()->get() as $index => $item) {
            $snapshot = $item->content_snapshot;
            $parts = ArabicSkillPrompt::sections($snapshot['prompt']);
            $this->get(route('student.arabic-skills.show', $session))->assertOk()
                ->assertSee('arabic-reading-title')->assertSee('arabic-reading-passage')
                ->assertSee(e($parts['question']), false)->assertSee(e($parts['context']['passage']), false)
                ->assertDontSee('اختر إجابتك ثم تحقق منها لعرض الشرح.')
                ->assertDontSee('بُرهان | الكتابة العلمية')->assertDontSee('arabic-reading-metadata')
                ->assertDontSee(e($snapshot['explanation']), false)->assertDontSee('session-feedback-toast');
            $response = $snapshot['answer'];
            if ($snapshot['type'] === 'matching') {
                $response['matches'][0]['option_id'] = $response['matches'][0]['option_id'] === 'a' ? 'b' : 'a';
            } elseif ($index === 1 || $index === 6) {
                $response['option_id'] = collect($snapshot['options'])->first(fn ($option) => $option['id'] !== $snapshot['answer']['option_id'])['id'];
            }
            $this->post(route('student.arabic-skills.answer', [$session, $item->id]), ['response' => $response])->assertRedirect();
            $page = $this->get(route('student.arabic-skills.show', $session))->assertOk()
                ->assertSee('session-feedback-toast')->assertSee('عرض الإجابة والشرح')->assertSee(e($snapshot['explanation']), false);
            if ($snapshot['type'] === 'matching') {
                $page->assertSee('إجابة صحيحة جزئيًا')->assertSee('3 / 4')->assertSee('عرض النتيجة');
            } else {
                $page->assertDontSee('2 / 2')->assertDontSee('0 / 2')->assertSee('arabic-choice-correct');
                if ($index === 1 || $index === 6) {
                    $page->assertSee('إجابة غير صحيحة')->assertSee('arabic-choice-incorrect');
                } else {
                    $page->assertSee('إجابة صحيحة');
                }
            }
            $this->post(route('student.arabic-skills.next', [$session, $item->id]))->assertRedirect();
        }
        $this->assertSame('completed', $session->fresh()->status);
        $this->assertSame(13, $session->fresh()->earned_points);
        $this->get(route('student.arabic-skills.results', $session))->assertOk()->assertSee('13 / 18');
    }
}
