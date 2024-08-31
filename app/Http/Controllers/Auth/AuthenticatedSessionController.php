<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function loginStudent(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is a student
        if ($user->role !== 'student') {
            Auth::logout();
            if ($request->cart) {
                alert()->error('البريد الإلكتروني أو كلمة المرور غير صحيح !')->timerProgressBar();
                return redirect()->route('cart.index');
            }
            return redirect()->route('login')->withErrors(['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيح !']);
        }

        $request->session()->regenerate();

        toast('تم تسجيل الدخول بنجاح !', 'success');

        if ($request->cart) {
            return redirect()->route('cart.index');
        }

        return redirect()->route('student.courses.index');
    }

    // public function loginAdmin(LoginRequest $request): RedirectResponse
    // {
    //     $request->authenticate();

    //     // Get the authenticated user
    //     $user = Auth::user();

    //     // Check if the user is an admin
    //     if ($user->role !== 'admin') {
    //         Auth::logout();
    //         return redirect()->route('admin.login')->withErrors(['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيح !']);
    //     }

    //     $request->session()->regenerate();
    //     toast('تم تسجيل الدخول بنجاح !', 'success');
    //     return redirect()->route('admin.dashboard');
    // }

    public function loginAdmin(LoginRequest $request)
    {
        $request->authenticate();
        $user = Auth::user();

        if ($user->role !== 'admin') {
            Auth::logout();
            return redirect()->route('admin.login')->withErrors(['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيح !']);
        }

        try {
            $twilio = app(Client::class);
            $verification = $twilio->verify->v2->services(config('services.twilio.verify_sid'))
                ->verifications
                ->create($user->phone_number, 'sms');

            // If successful, proceed with your existing logic
            // Store user ID in session
            session(['admin_2fa_id' => $user->id]);

            // Logout user and redirect to 2FA verification page
            Auth::logout();
            return redirect()->route('admin_otp_page');
        } catch (TwilioException $e) {
            // Log the error
            Log::error('Twilio error: ' . $e->getMessage());

            // Logout the user
            Auth::logout();

            // Redirect back to admin login page with error
            return redirect()->route('admin.login')->withErrors(['email' => 'حدث خطأ أثناء إرسال رمز التحقق. يرجى المحاولة مرة أخرى.']);
        } catch (\Exception $e) {
            // Log any other unexpected errors
            Log::error('Unexpected error during 2FA: ' . $e->getMessage());

            // Logout the user
            Auth::logout();

            // Redirect back to admin login page with a generic error
            return redirect()->route('admin.login')->withErrors(['error' => 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.']);
        }
    }

    public function verifyOTP(Request $request)
    {
        $request->validate([
            'verification_code' => 'required|string|size:6',
        ]);

        $adminId = session('admin_2fa_id');
        $admin = User::findOrFail($adminId);

        $twilio = app(Client::class);
        $verification_check = $twilio->verify->v2->services(config('services.twilio.verify_sid'))
            ->verificationChecks
            ->create([
                'to' => $admin->phone_number,
                'code' => $request->verification_code
            ]);

        if ($verification_check->status === 'approved') {
            Auth::login($admin);
            session()->forget('admin_2fa_id');
            toast('تم تسجيل الدخول بنجاح !', 'success');
            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['verification_code' => 'رمز التحقق غير صحيح']);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
