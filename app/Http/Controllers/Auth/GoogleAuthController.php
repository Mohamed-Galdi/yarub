<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialIdentity;
use App\Models\User;
use App\Services\Auth\StudentDestination;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public static function configured(): bool
    {
        return (bool) (config('services.google.client_id') && config('services.google.client_secret') && config('services.google.redirect'));
    }

    public function account(Request $request)
    {
        return view('auth.google-account', [
            'linked' => SocialIdentity::where('user_id', $request->user()->id)->where('provider', 'google')->exists(),
        ]);
    }

    public function redirect(Request $request)
    {
        return $this->begin($request, null);
    }

    public function link(Request $request)
    {
        $request->validate(['current_password' => ['required', 'string', 'current_password:web']]);
        abort_unless($request->user()->has_local_password, 403);

        return $this->begin($request, $request->user()->id);
    }

    private function begin(Request $request, ?int $userId)
    {
        if (! self::configured()) {
            return redirect()->route($userId ? 'google.account' : 'login')
                ->withErrors(['google' => 'تسجيل الدخول عبر Google غير متاح حالياً. حاول لاحقاً أو استخدم البريد وكلمة المرور.']);
        }

        $request->session()->put('google.flow', ['user_id' => $userId, 'started_at' => time()]);

        // Socialite stores and validates state in this same session; never use stateless().
        return Socialite::driver('google')->setScopes(['openid', 'email', 'profile'])
            ->with(['prompt' => 'select_account'])->redirect();
    }

    public function callback(Request $request)
    {
        $flow = $request->session()->pull('google.flow');
        $returnRoute = $request->user()?->role === 'student' ? 'google.account' : 'login';
        $failure = fn (string $message) => redirect()->route($returnRoute)->withErrors(['google' => $message]);

        if (! is_array($flow) || time() - ($flow['started_at'] ?? 0) > 600 || $request->has('error')) {
            $request->session()->forget('state');
            return $failure('لم يكتمل الاتصال بـ Google أو انتهت صلاحيته. ابدأ المحاولة من جديد.');
        }

        try {
            $google = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            // Provider exceptions can contain tokens or credentials: never flash or log their bodies.
            $request->session()->forget('state');
            return $failure('تعذر التحقق من Google. ابدأ المحاولة من جديد.');
        }

        $providerId = $google->getId();
        $email = Str::lower(trim((string) $google->getEmail()));
        if (! is_string($providerId) || $providerId === '' || strlen($providerId) > 255
            || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 255
            || ($google->user['email_verified'] ?? false) !== true) {
            return $failure('يلزم حساب Google ببريد إلكتروني موثّق لإكمال الاتصال.');
        }

        $linkUserId = $flow['user_id'] ?? null;
        if ($linkUserId !== null && (! $request->user() || $request->user()->id !== $linkUserId || $request->user()->role !== 'student')) {
            return $failure('تغيّرت جلسة الحساب. سجّل الدخول ثم أعد الربط.');
        }
        if ($linkUserId === null && Auth::check()) {
            return $failure('أنت مسجّل الدخول بالفعل. استخدم صفحة ربط الحساب.');
        }

        try {
            $user = DB::transaction(function () use ($providerId, $email, $google, $linkUserId) {
                $identity = SocialIdentity::where('provider', 'google')->where('provider_id', $providerId)->first();
                if ($identity) {
                    $user = $identity->user()->first();
                    abort_unless($user && $user->role === 'student' && ($linkUserId === null || $user->id === $linkUserId), 403);
                    return $user;
                }

                if ($linkUserId !== null) {
                    $user = User::whereKey($linkUserId)->where('role', 'student')->lockForUpdate()->firstOrFail();
                    // Link only the matching, verified Google email after local-password proof.
                    abort_unless(Str::lower($user->email) === $email, 403);
                } else {
                    // Include blocked (soft-deleted) accounts. Email alone is never ownership proof.
                    if (User::withTrashed()->whereRaw('LOWER(email) = ?', [$email])->exists()) {
                        return null;
                    }
                    $user = new User();
                    $user->forceFill([
                        'name' => mb_substr(trim((string) $google->getName()) ?: 'طالب يعرب', 0, 255),
                        'email' => $email,
                        'password' => Hash::make(Str::random(64)),
                        'has_local_password' => false,
                        'email_verified_at' => now(),
                        'role' => 'student',
                        'avatar' => 'storage/users_avatars/default.png',
                    ])->save();
                    event(new Registered($user));
                }

                SocialIdentity::create(['user_id' => $user->id, 'provider' => 'google', 'provider_id' => $providerId]);
                return $user;
            });
        } catch (QueryException $e) {
            return $failure('تعذر إكمال الربط. قد يكون الحساب مرتبطاً بالفعل. أعد المحاولة من صفحة ربط الحساب.');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $e) {
            return $failure('لا يمكن استخدام حساب Google هذا مع هذا الحساب. استخدم حساب الطالب الصحيح.');
        }

        if (! $user) {
            $request->session()->put('google.link_after_login', true);
            return $failure('يوجد حساب بهذا البريد. سجّل الدخول بكلمة المرور أولاً، ثم افتح «ربط Google». إذا نسيت كلمة المرور، استعدها أولاً.');
        }

        if ($linkUserId !== null) {
            return redirect()->route('google.account')->with('status', 'تم ربط حساب Google بنجاح.');
        }

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->to(StudentDestination::pull($request));
    }
}
