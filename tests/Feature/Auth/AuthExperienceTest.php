<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Tests\AuthTestCase;

class AuthExperienceTest extends AuthTestCase
{
    public function test_all_authentication_screens_render_arabic_labels_and_forms(): void
    {
        $pages = [
            'login' => '/login', 'register' => '/register', 'admin' => '/yarub-moshref-center',
            'forgot' => '/forgot-password', 'reset' => '/reset-password/example-token?email=student%40example.com',
        ];
        foreach ($pages as $name => $url) {
            $response = $this->get($url)->assertOk()->assertSee('dir="rtl"', false)->assertSee('data-auth-form', false);
            $this->exportPreview($name, $response->getContent());
        }
        $response = $this->withSession(['errors' => (new ViewErrorBag)->put('default', new MessageBag(['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.']))])
            ->get('/login')->assertOk()->assertSee('role="alert"', false);
        $this->exportPreview('error', $response->getContent());
        session()->forget('errors');

        $user = User::factory()->create(['email' => 'student@example.com']);
        $this->actingAs($user);
        foreach (['verify' => '/verify-email', 'confirm' => '/confirm-password', 'google-account' => '/auth/google/account'] as $name => $url) {
            $response = $this->get($url)->assertOk();
            $this->exportPreview($name, $response->getContent());
        }
        $user->forceFill(['has_local_password' => false])->save();
        $this->get('/confirm-password')->assertOk()->assertSee('عيّن كلمة مرور أولاً');
    }

    private function exportPreview(string $name, string $html): void
    {
        // Optional visual fixtures contain only disposable test data and no live integrations.
        if (! $directory = getenv('AUTH_PREVIEW_DIR')) return;
        if (! is_dir($directory)) mkdir($directory, 0777, true);
        $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $assets = '<link rel="stylesheet" href="/build/'.$manifest['resources/css/auth.css']['file'].'">'
            .'<script type="module" src="/build/'.$manifest['resources/js/auth.js']['file'].'"></script>';
        $html = str_replace('http://localhost/', '/', $html);
        file_put_contents($directory.'/'.$name.'.html', str_replace('</head>', $assets.'</head>', $html));
    }

    public function test_student_intended_destination_is_preserved_and_external_redirects_rejected(): void
    {
        $user = User::factory()->create();
        $destination = route('student.qudrat.category', ['category' => 3]);
        $this->withSession(['url.intended' => $destination])->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect($destination);
        $this->post('/logout');
        foreach (['https://evil.example/steal', 'http://localhost.evil.example/student-dashboard', 'http://localhost/student-dashboard/\\evil.example'] as $unsafe) {
            $this->withSession(['url.intended' => $unsafe])->post('/login', ['email' => $user->email, 'password' => 'password'])
                ->assertRedirect(route('student.courses.index'));
            $this->post('/logout');
        }
    }

    public function test_registration_validates_fields_role_and_session_rotation(): void
    {
        $this->post('/register', [])->assertSessionHasErrors(['first_name', 'last_name', 'email', 'password']);
        $this->withSession(['journey' => 'retained']);
        $before = session()->getId();
        $this->post('/register', ['first_name' => 'طالب', 'last_name' => 'يعرب', 'email' => 'new@example.com',
            'password' => 'password', 'password_confirmation' => 'password', 'role' => 'admin'])
            ->assertRedirect(route('student.courses.index'));
        $this->assertSame('student', auth()->user()->role);
        $this->assertNotSame($before, session()->getId());
        $this->assertSame('retained', session('journey'));
    }

    public function test_local_login_remember_and_logout_rotate_session_and_csrf(): void
    {
        $user = User::factory()->create();
        $this->withSession(['private' => 'value']);
        $before = session()->getId();
        $this->post('/login', ['email' => $user->email, 'password' => 'password', 'remember' => '1'])
            ->assertRedirect(route('student.courses.index'))->assertCookie(auth()->getRecallerName());
        $this->assertNotSame($before, session()->getId());
        $this->assertNotEmpty($user->fresh()->remember_token);
        $csrf = session()->token();
        $this->post('/logout')->assertRedirect(route('home'));
        $this->assertGuest();
        $this->assertNull(session('private'));
        $this->assertNotSame($csrf, session()->token());
    }

    public function test_login_throttles_repeated_invalid_credentials(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/login', ['email' => 'missing@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/login', ['email' => 'missing@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertMatchesRegularExpression('/انتظر [0-9]+ ثانية/u', session('errors')->first('email'));
    }

    public function test_admin_login_and_role_boundaries_are_preserved(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->post('/login', ['email' => $admin->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/yarub-moshref-center', ['email' => $admin->email, 'password' => 'password'])->assertRedirect(route('admin.dashboard'));
        $this->get('/login')->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
        $this->post('/logout');
        $student = User::factory()->create();
        $this->post('/yarub-moshref-center', ['email' => $student->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->get('/student-dashboard/courses')->assertRedirect(route('login'));
        $this->actingAs($student)->get('/login')->assertRedirect(route('student.courses.index'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('home'));
    }

    public function test_deleted_students_cannot_login(): void
    {
        $user = User::factory()->create();
        $user->delete();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_password_reset_supports_google_only_accounts_and_rejects_expired_token(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['password' => Hash::make('unguessable-placeholder'), 'has_local_password' => false])->save();
        $token = Password::createToken($user);
        $this->post('/reset-password', ['email' => $user->email, 'token' => $token, 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertRedirect(route('login'));
        $this->assertTrue($user->fresh()->has_local_password);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->post('/reset-password', ['email' => $user->email, 'token' => $token, 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertSessionHasErrors('email');
        $token = Password::createToken($user);
        $this->travel(61)->minutes();
        $this->post('/reset-password', ['email' => $user->email, 'token' => $token, 'password' => 'new-password', 'password_confirmation' => 'new-password'])->assertSessionHasErrors('email');
    }

    public function test_verification_resend_and_expired_link_recovery(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->actingAs($user)->post('/email/verification-notification')->assertSessionHas('status', 'verification-link-sent');
        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\VerifyEmail::class);
        $url = URL::temporarySignedRoute('verification.verify', now()->subMinute(), ['id' => $user->id, 'hash' => sha1($user->email)]);
        $response = $this->get($url)->assertForbidden()->assertSee('رابط التحقق غير صالح');
        $this->exportPreview('expired-verification', $response->getContent());
    }

    public function test_google_student_can_request_password_setup_while_signed_in(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $user->forceFill(['has_local_password' => false])->save();
        $this->actingAs($user)->post('/auth/password-email')->assertSessionHas('status');
        Notification::assertSentTo($user, \Illuminate\Auth\Notifications\ResetPassword::class);
    }

    public function test_profile_password_update_validates_and_preserves_json_contract(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->putJson('/student-dashboard/profile/password', ['current_password' => 'password', 'new_password' => 'x'])
            ->assertUnprocessable()->assertJsonStructure(['error'])->assertJsonValidationErrors('new_password');
        $this->putJson('/student-dashboard/profile/password', ['current_password' => 'wrong', 'new_password' => 'new-password'])
            ->assertUnprocessable()->assertJsonStructure(['error']);
        $this->putJson('/student-dashboard/profile/password', ['current_password' => 'password', 'new_password' => 'new-password'])
            ->assertOk()->assertJsonStructure(['message']);
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertGuest();
    }

    public function test_missing_email_reset_request_has_same_public_status_as_existing_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status', __('passwords.sent'));
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status', __('passwords.sent'));
        $this->post('/forgot-password', ['email' => 'missing@example.com'])->assertSessionHas('status', __('passwords.sent'));
    }
}
