<?php

namespace Tests\Feature\ArabicSkills;

use App\Http\Middleware\LogVisit;
use App\Models\ArabicSkillBank;
use App\Models\ArabicSkillSession;
use App\Models\User;
use App\Services\ArabicSkills\ContentImporter;
use App\Services\ArabicSkills\ContentValidator;
use App\Services\ArabicSkills\Practice;
use Tests\ArabicSkillsTestCase;

class StudentPagesTest extends ArabicSkillsTestCase
{
    private User $student;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        $this->student = User::create(['name' => 'Local student', 'email' => 'pages@example.invalid', 'password' => 'local', 'role' => 'student']);
        app(ContentImporter::class)->import(app(ContentValidator::class)->validateDirectory(base_path('arabic_skills_content')));
    }

    private function modelSession(): ArabicSkillSession
    {
        $bank = ArabicSkillBank::where('slug', 'nasaq')->firstOrFail();

        return app(Practice::class)->start($this->student, $bank, 'model', $bank->models()->firstOrFail());
    }

    public function test_stopped_banks_keep_owned_attempts_and_history_discoverable_without_new_starts(): void
    {
        $session = $this->modelSession();
        $bank = $session->bank()->firstOrFail();
        ArabicSkillBank::query()->update(['is_active' => false]);
        $other = User::create(['name' => 'No attempts', 'email' => 'no-attempts@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->actingAs($other)->get(route('student.arabic-skills.index'))->assertOk()->assertSee('لا توجد بنوك متاحة');
        $this->actingAs($this->student)->get(route('student.arabic-skills.index'))->assertOk()->assertSee($bank->title)->assertDontSee('لا توجد بنوك متاحة');
        $this->get(route('student.arabic-skills.bank', $bank))->assertOk()->assertSee(route('student.arabic-skills.show', $session), false);
        $this->post(route('student.arabic-skills.start', $bank), ['mode' => 'model', 'model_id' => $session->model_id])->assertRedirect(route('student.arabic-skills.show', $session));
        $this->postJson(route('student.arabic-skills.start', $bank), ['mode' => 'practice'])->assertUnprocessable();
        $practice = app(Practice::class);
        foreach ($session->items()->get() as $item) {
            $practice->answer($this->student, $session, $item->id, $item->content_snapshot['answer']);
            $practice->next($this->student, $session, $item->id);
        }
        $this->get(route('student.arabic-skills.index'))->assertOk()->assertSee($bank->title)->assertSee('البنك متوقف للتدريبات الجديدة.')->assertDontSee('يلزم توفر عشرة');
        $this->get(route('student.arabic-skills.bank', $bank))->assertOk()->assertSee(route('student.arabic-skills.results', $session), false);
        $this->get(route('student.arabic-skills.results', $session))->assertOk()->assertSee('60 / 60');
        $this->postJson(route('student.arabic-skills.start', $bank), ['mode' => 'model', 'model_id' => $session->model_id])->assertUnprocessable();
        $this->assertSame(1, ArabicSkillSession::count());
    }

    public function test_student_landing_bank_models_and_navigation_use_real_content(): void
    {
        $this->get(route('student.arabic-skills.index'))->assertRedirect(route('login'));
        $this->actingAs($this->student)->get(route('student.arabic-skills.index'))->assertOk()
            ->assertSee('مهارات اللغة العربية')->assertSee('نَسَق')->assertSee('رَسْم')->assertSee('بَيان')->assertSee('مِداد')->assertSee('بُرهان')
            ->assertSee('10 أسئلة في التدريب')->assertSee('300');
        $this->get(route('student.arabic-skills.bank', ArabicSkillBank::firstOrFail()))->assertOk()
            ->assertSee('النموذج 10')->assertSee('سجل التدريب')->assertSee('لم تبدأ تدريبًا');
        $this->assertSame(0, ArabicSkillSession::count());
    }

    public function test_real_model_flow_has_three_choices_string_booleans_and_independent_matching(): void
    {
        $session = $this->modelSession();
        $this->actingAs($this->student);
        $items = $session->items()->get();
        foreach ($items as $item) {
            $snapshot = $item->content_snapshot;
            $response = $this->get(route('student.arabic-skills.show', $session))->assertOk()->assertSee(e($snapshot['prompt']), false)
                ->assertDontSee(e($snapshot['explanation']), false)->assertDontSee('إظهار تلميح')->assertDontSee('content_snapshot');
            if ($snapshot['type'] === 'matching') {
                $response->assertSee('اختر المطابقة لكل عبارة')->assertSee('response[matches][0][option_id]', false)->assertSee('response[matches][1][option_id]', false);
            } elseif ($snapshot['type'] === 'true_false') {
                $response->assertSee('value="true"', false)->assertSee('value="false"', false);
            } else {
                $this->assertSame(3, substr_count($response->getContent(), 'type="radio"'));
            }
            $this->post(route('student.arabic-skills.answer', [$session, $item->id]), ['response' => $snapshot['answer']])->assertRedirect(route('student.arabic-skills.show', $session));
            $this->get(route('student.arabic-skills.show', $session))->assertOk()->assertSee(e($snapshot['explanation']), false)->assertSee('إجابة صحيحة');
            $this->post(route('student.arabic-skills.next', [$session, $item->id]))->assertRedirect();
        }
        $this->get(route('student.arabic-skills.results', $session))->assertOk()->assertSee('نتيجة التدريب')->assertSee('60 / 60')->assertSee('100٪');
        $this->assertSame('completed', $session->fresh()->status);
    }

    public function test_every_session_endpoint_rejects_other_students_and_guests(): void
    {
        $session = $this->modelSession();
        $item = $session->items()->firstOrFail();
        $other = User::create(['name' => 'Other', 'email' => 'pages-other@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->get(route('student.arabic-skills.show', $session))->assertRedirect(route('login'));
        $this->actingAs($other);
        foreach (['show', 'results'] as $action) {
            $this->get(route('student.arabic-skills.'.$action, $session))->assertForbidden();
        }
        foreach (['answer', 'next', 'hint'] as $action) {
            $this->post(route('student.arabic-skills.'.$action, [$session, $item->id]), ['response' => $item->content_snapshot['answer']])->assertForbidden();
        }
        $this->post(route('student.arabic-skills.abandon', $session))->assertForbidden();
        $this->assertNull($item->fresh()->submitted_at);
        $this->assertSame('active', $session->fresh()->status);
    }

    public function test_invalid_submissions_recover_without_revealing_answers_and_partial_results_use_points(): void
    {
        $session = $this->modelSession();
        $this->actingAs($this->student);
        $first = $session->items()->firstOrFail();
        $this->postJson(route('student.arabic-skills.answer', [$session, $first->id]), ['response' => $first->content_snapshot['answer'], 'score' => 999])->assertUnprocessable();
        $this->get(route('student.arabic-skills.results', $session))->assertRedirect(route('student.arabic-skills.show', $session));
        foreach ($session->items()->get() as $item) {
            $answer = $item->content_snapshot['answer'];
            if (isset($answer['matches'])) {
                $this->postJson(route('student.arabic-skills.answer', [$session, $item->id]), ['response' => ['matches' => [$answer['matches'][0]]]])->assertUnprocessable();
                $this->assertNull($item->fresh()->submitted_at);
                $answer['matches'][1]['option_id'] = $answer['matches'][0]['option_id'];
            }
            $this->post(route('student.arabic-skills.answer', [$session, $item->id]), ['response' => $answer])->assertRedirect();
            $this->post(route('student.arabic-skills.next', [$session, $item->id]))->assertRedirect();
        }
        $this->get(route('student.arabic-skills.results', $session))->assertOk()->assertSee('إجابة صحيحة جزئيًا')->assertSee('51 / 60')->assertSee('85٪');
    }

    public function test_abandonment_history_unavailable_models_and_hints_are_distinct_states(): void
    {
        $session = $this->modelSession();
        $this->actingAs($this->student);
        $item = $session->items()->firstOrFail();
        $this->postJson(route('student.arabic-skills.hint', [$session, $item->id]))->assertUnprocessable();
        $this->post(route('student.arabic-skills.abandon', $session))->assertRedirect(route('student.arabic-skills.bank', $session->bank_id));
        $this->get(route('student.arabic-skills.bank', $session->bank_id))->assertOk()->assertSee('غير مكتمل');
        $item->question()->firstOrFail()->update(['is_active' => false]);
        $this->get(route('student.arabic-skills.bank', $session->bank_id))->assertSee('النموذج غير متاح');
        $this->post(route('student.arabic-skills.start', $session->bank_id), ['mode' => 'model', 'model_id' => $session->model_id])->assertSessionHasErrors('practice');
    }

    public function test_genuine_hint_is_requested_separately_and_never_reveals_explanations(): void
    {
        $bank = ArabicSkillBank::where('slug', 'nasaq')->firstOrFail();
        $question = $bank->questions()->where('number', 1)->firstOrFail();
        $question->update(['hint' => 'فكّر فيمن قام بالفعل.']);
        $session = $this->modelSession();
        $item = $session->items()->firstOrFail();
        $this->actingAs($this->student)->get(route('student.arabic-skills.show', $session))->assertOk()
            ->assertSee('إظهار تلميح')->assertDontSee('فكّر فيمن قام بالفعل.')->assertDontSee(e($question->explanation), false);
        $this->postJson(route('student.arabic-skills.hint', [$session, $item->id]))->assertOk()
            ->assertExactJson(['item_id' => $item->id, 'hint' => 'فكّر فيمن قام بالفعل.']);
        $this->assertNull($item->fresh()->submitted_at);
        $this->assertSame(0, $session->fresh()->earned_points);
    }
}
