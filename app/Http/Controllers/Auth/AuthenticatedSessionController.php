<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use RealRashid\SweetAlert\Facades\Alert;

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

    public function loginAdmin(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        // Get the authenticated user
        $user = Auth::user();

        // Check if the user is an admin
        if ($user->role !== 'admin') {
            Auth::logout();
            return redirect()->route('admin.login')->withErrors(['email' => 'البريد الإلكتروني أو كلمة المرور غير صحيح !']);
        }

        $request->session()->regenerate();
        toast('تم تسجيل الدخول بنجاح !', 'success');
        return redirect()->route('admin.dashboard');
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
