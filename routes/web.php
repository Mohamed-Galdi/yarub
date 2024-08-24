<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CoursesPageController;
use App\Http\Controllers\HomePagesController;
use App\Http\Controllers\LessonsPageController;
use App\Http\Controllers\SubController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware(['log.visit'])->group(function () {
    // ///////////// Home Routes //////////////
    Route::get('/', [HomePagesController::class, 'homePage'])->name('home');
    Route::get('/about', [HomePagesController::class, 'aboutPage'])->name('about');
    Route::get('/contact', [HomePagesController::class, 'contactPage'])->name('contact');

    // Courses Routes
    Route::get('/courses', [CoursesPageController::class, 'coursesPage'])->name('courses');
    Route::get('/courses/{course}', [CoursesPageController::class, 'coursePage'])->name('course');

    // Lessons Routes
    Route::get('/lessons', [LessonsPageController::class, 'lessonsPage'])->name('lessons');
    Route::get('/lessons/{lesson}', [LessonsPageController::class, 'coursePage'])->name('lesson');

    // ///////////// Cart Routes //////////////
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/update', [CartController::class, 'updateCart'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.apply-coupon');

    // ///////////// Subscription Routes ///////////////
    Route::get('/subscription', [SubController::class, 'subscribe'])->name('subscription');
    Route::get('/thanks', [HomePagesController::class, 'thanksPage'])->name('thanks');
});

// Include Admin Routes
require __DIR__ . '/admin.php';

// Include Student Routes
require __DIR__ . '/student.php';

// Auth Routes
require __DIR__ . '/auth.php';
