<?php

namespace Tests\Feature\Auth;

use App\Models\SocialIdentity;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Tests\AuthTestCase;

class GoogleAuthenticationTest extends AuthTestCase
{
    private function provider(array $claims = []): void
    {
        // Exercise Socialite's real state validation, token exchange and Google mapping.
        $request = request();
        $request->setLaravelSession(app('session')->driver());
        $provider = new GoogleProvider($request, 'test-client', 'test-secret', 'http://localhost/auth/google/callback');
        $provider->setHttpClient(new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['access_token' => 'fake-access-token', 'expires_in' => 3600])),
            new Response(200, [], json_encode(array_merge([
                'sub' => 'google-123', 'name' => 'طالب جديد', 'email' => 'student@example.com', 'email_verified' => true,
            ], $claims))),
        ]))]));
        // The provider must see the actual callback request, including its session.
        $factory = \Mockery::mock(\Laravel\Socialite\Contracts\Factory::class);
        Socialite::swap($factory);
        $factory->shouldReceive('driver')->with('google')->andReturnUsing(function () use ($provider) {
            $reflection = new \ReflectionProperty($provider, 'request');
            $reflection->setValue($provider, request());
            return $provider;
        });
    }

    private function completeGoogle(array $session = [])
    {
        return $this->withSession(array_merge([
            'state' => 'expected-state',
            'google.flow' => ['user_id' => null, 'started_at' => time()],
        ], $session))->get('/auth/google/callback?state=expected-state&code=fake-code');
    }

    public function test_new_google_student_and_repeat_login_use_one_identity(): void
    {
        $this->provider();
        $this->completeGoogle()->assertRedirect(route('student.courses.index'));
        $user = User::firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame('student', $user->role);
        $this->assertFalse($user->has_local_password);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseCount('social_identities', 1);
        $this->post('/logout');

        $this->provider();
        $this->completeGoogle()->assertRedirect(route('student.courses.index'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_identities', 1);
    }

    public function test_existing_email_is_not_automatically_linked(): void
    {
        User::factory()->create(['email' => 'STUDENT@example.com']);
        $this->provider();
        $this->completeGoogle()->assertRedirect(route('login'))->assertSessionHasErrors('google');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_identities', 0);
    }

    public function test_authenticated_password_proof_links_matching_google_account(): void
    {
        $user = User::factory()->create(['email' => 'student@example.com']);
        $this->actingAs($user)->post('/auth/google/link', ['current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->post('/auth/google/link', ['current_password' => 'password'])->assertRedirect();
        $this->assertSame($user->id, session('google.flow.user_id'));
        $this->provider();
        $this->completeGoogle(['google.flow' => ['user_id' => $user->id, 'started_at' => time()]])
            ->assertRedirect(route('google.account'));
        $this->assertDatabaseHas('social_identities', ['user_id' => $user->id, 'provider_id' => 'google-123']);
        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_link_rejects_different_email_or_identity_owned_by_another_student(): void
    {
        $user = User::factory()->create(['email' => 'other@example.com']);
        $this->actingAs($user);
        $this->provider();
        $this->completeGoogle(['google.flow' => ['user_id' => $user->id, 'started_at' => time()]])->assertSessionHasErrors('google');
        $this->assertDatabaseCount('social_identities', 0);

        $owner = User::factory()->create(['email' => 'student@example.com']);
        SocialIdentity::create(['user_id' => $owner->id, 'provider' => 'google', 'provider_id' => 'google-123']);
        $this->provider();
        $this->completeGoogle(['google.flow' => ['user_id' => $user->id, 'started_at' => time()]])->assertSessionHasErrors('google');
        $this->assertDatabaseCount('social_identities', 1);
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_cannot_authenticate_admin_or_blocked_student(): void
    {
        foreach (['admin', 'student'] as $role) {
            $user = User::factory()->create(['email' => 'student@example.com', 'role' => $role]);
            SocialIdentity::create(['user_id' => $user->id, 'provider' => 'google', 'provider_id' => 'google-123']);
            if ($role === 'student') $user->delete();
            $this->provider();
            $this->completeGoogle()->assertSessionHasErrors('google');
            $this->assertGuest();
            $user->forceDelete();
        }
    }

    public function test_blocked_email_cannot_create_replacement_account(): void
    {
        $user = User::factory()->create(['email' => 'student@example.com']);
        $user->delete();
        $this->provider();
        $this->completeGoogle()->assertSessionHasErrors('google');
        $this->assertSame(1, User::withTrashed()->count());
        $this->assertDatabaseCount('social_identities', 0);
    }

    public function test_unverified_and_missing_provider_claims_are_rejected(): void
    {
        foreach ([['email_verified' => false], ['email' => null], ['sub' => null]] as $claims) {
            $this->provider($claims);
            $this->completeGoogle()->assertSessionHasErrors('google');
            $this->assertGuest();
        }
        $this->assertDatabaseCount('users', 0);
    }

    public function test_invalid_state_expired_flow_and_cancel_are_recoverable(): void
    {
        $this->provider();
        $this->completeGoogle(['state' => 'wrong-state'])->assertSessionHasErrors('google');
        $this->assertGuest();
        $this->completeGoogle(['google.flow' => ['user_id' => null, 'started_at' => time() - 601]])->assertSessionHasErrors('google');
        $this->get('/auth/google/callback?error=access_denied')->assertSessionHasErrors('google');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_link_callback_cannot_switch_to_another_local_session(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->provider();
        $this->completeGoogle(['google.flow' => ['user_id' => $user->id + 1, 'started_at' => time()]])->assertSessionHasErrors('google');
        $this->assertDatabaseCount('social_identities', 0);
    }

    public function test_redirect_requests_only_identity_scopes_and_stores_state(): void
    {
        $response = $this->get('/auth/google');
        $response->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('openid email profile', $query['scope']);
        $this->assertNotEmpty(session('state'));
        $this->assertSame(session('state'), $query['state']);
        $this->assertStringNotContainsString('test-secret', $response->headers->get('Location'));
    }

    public function test_admin_cannot_start_linking_and_has_no_google_button(): void
    {
        $this->get('/yarub-moshref-center')->assertOk()->assertDontSee('المتابعة باستخدام');
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user)->post('/auth/google/link', ['current_password' => 'password'])->assertRedirect(route('home'));
        $this->assertDatabaseCount('social_identities', 0);
    }

    public function test_google_setup_missing_is_handled_without_redirect_to_provider(): void
    {
        config(['services.google.client_id' => null]);
        $this->get('/auth/google')->assertRedirect(route('login'))->assertSessionHasErrors('google');
        foreach (['/login', '/register'] as $page) {
            $this->get($page)->assertOk()->assertSee('المتابعة باستخدام')->assertSee('auth-google')->assertSee('<svg', false);
        }
    }

    public function test_provider_outage_is_handled_without_exposing_response_or_secret(): void
    {
        Socialite::shouldReceive('driver')->with('google')->andThrow(new \RuntimeException('sensitive-provider-response'));
        $this->completeGoogle()->assertRedirect(route('login'))->assertSessionHasErrors('google');
        $this->assertStringNotContainsString('sensitive-provider-response', session('errors')->first('google'));
        $this->assertGuest();
    }
}
