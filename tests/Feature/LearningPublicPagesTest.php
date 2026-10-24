<?php

namespace Tests\Feature;

use App\Http\Middleware\LogVisit;
use App\Models\ArabicSkillSession;
use App\Models\ContactPage;
use App\Models\HomePage;
use App\Models\HomePageReview;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\ArabicSkillsTestCase;

class LearningPublicPagesTest extends ArabicSkillsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(LogVisit::class);
        ContactPage::firstOrCreate([], array_fill_keys(['commercial_registration_no', 'phone_number', 'email', 'address', 'whatsapp_number', 'instagram', 'tiktok', 'snapchat'], 'local-only'));
    }

    public function test_public_pages_explain_real_features_and_use_only_illustrative_local_demos(): void
    {
        $queries = [];
        DB::listen(function ($query) use (&$queries) {
            $queries[] = $query->sql;
        });
        $this->get(route('qudrat'))->assertOk()->assertSee('1,200 سؤال أساسي')->assertSee('التناظر اللفظي')->assertSee('استيعاب المقروء')
            ->assertSee('مثال توضيحي للتجربة')->assertSee('سجّل الدخول وابدأ التدريب')->assertSee(route('student.qudrat.index'), false)->assertDontSee('mpf/1.14.0');
        $this->get(route('arabic-skills'))->assertOk()->assertSee('1,500 سؤال أساسي')->assertSee('نَسَق')->assertSee('بُرهان')
            ->assertSee('بحد أقصى 60 نقطة')->assertSee('لكل مطابقة صحيحة نقطة')->assertSee(route('student.arabic-skills.index'), false)->assertDontSee('content_snapshot');
        $this->assertSame([], array_values(array_filter($queries, fn ($query) => preg_match('/(?:arabic_skill_|qudrat_)/', $query))));
        $this->assertSame(0, ArabicSkillSession::count());
    }

    public function test_removed_lessons_listing_redirects_and_public_navigation_replaces_it(): void
    {
        $this->get('/lessons')->assertStatus(301)->assertRedirect('/qudrat');
        HomePage::firstOrCreate([], array_fill_keys((new HomePage)->getFillable(), 'يعرب'));
        HomePageReview::create(['review' => 'مراجعة محلية للاختبار', 'reviewer_name' => 'طالب تجريبي', 'reviewer_image' => 'assets/users-img/man-1.webp', 'stars' => 5]);
        $this->get(route('home'))->assertOk()->assertSee('تدريبك يبدأ من حسابك.')->assertSee(route('qudrat'), false)
            ->assertSee(route('arabic-skills'), false)->assertDontSee('أبرز الشروحات')->assertDontSee('href="'.route('lessons').'"', false);
        $this->assertTrue(app('router')->getRoutes()->hasNamedRoute('lesson'));
    }

    public function test_guest_feature_buttons_return_to_selected_feature_after_student_login(): void
    {
        $user = User::create(['name' => 'Student', 'email' => 'public-student@example.invalid', 'password' => 'local', 'role' => 'student']);
        foreach (['student.qudrat.index', 'student.arabic-skills.index'] as $destination) {
            Auth::logout();
            $this->get(route($destination))->assertRedirect(route('login'))->assertSessionHas('url.intended', route($destination));
            $this->post(route('student.login'), ['email' => $user->email, 'password' => 'local'])->assertRedirect(route($destination));
        }
    }

    public function test_default_and_cart_login_redirects_are_preserved_and_other_intended_urls_are_not_added(): void
    {
        $user = User::create(['name' => 'Student', 'email' => 'public-default@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->withSession(['url.intended' => 'https://example.invalid'])->post(route('student.login'), ['email' => $user->email, 'password' => 'local'])->assertRedirect(route('student.courses.index'));
        Auth::logout();
        $this->withSession(['url.intended' => route('student.qudrat.index')])->post(route('student.login'), ['email' => $user->email, 'password' => 'local', 'cart' => 1])->assertRedirect(route('cart.index'));
    }

    public function test_student_and_admin_public_call_to_action_states_are_clear(): void
    {
        $student = User::create(['name' => 'Student', 'email' => 'public-auth@example.invalid', 'password' => 'local', 'role' => 'student']);
        $this->actingAs($student)->get(route('arabic-skills'))->assertOk()->assertSee('انتقل إلى مهارات اللغة العربية');
        $student->update(['role' => 'admin']);
        $this->actingAs($student->fresh())->get(route('qudrat'))->assertOk()->assertSee('استخدم حساب طالب')->assertDontSee('سجّل الدخول وابدأ التدريب');
    }
}
