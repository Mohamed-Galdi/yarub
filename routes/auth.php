<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\GoogleAuthController;

Route::get('auth/google', [GoogleAuthController::class, 'redirect'])->middleware(['guest', 'throttle:10,1'])->name('google.redirect');
Route::get('auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:20,1')->name('google.callback');
Route::get('auth/google/account', [GoogleAuthController::class, 'account'])->middleware(['auth', 'student'])->name('google.account');
Route::post('auth/google/link', [GoogleAuthController::class, 'link'])->middleware(['auth', 'student', 'throttle:5,1'])->name('google.link');
Route::post('auth/password-email', [PasswordResetLinkController::class, 'forStudent'])->middleware(['auth', 'student', 'throttle:5,1'])->name('student.password.email');

Route::middleware(['guest', 'log.visit'])->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register_page');

    Route::post('register', [RegisteredUserController::class, 'store'])
    ->middleware('throttle:5,1')
    ->name('register');

    Route::get('login', function () {return view('auth.login');})
    ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'loginStudent'])
    ->name('student.login');

    Route::get('yarub-moshref-center', function () {return view('auth.admin-login');})
    ->name('admin_login_page');

    Route::post('yarub-moshref-center', [AuthenticatedSessionController::class, 'loginAdmin'])
    ->name('admin.login');

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store'])->middleware('throttle:5,1');

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
