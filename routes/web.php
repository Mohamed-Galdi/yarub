<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\HomePagesController;
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

// ///////////// Home Routes //////////////
Route::get('/', [HomePagesController::class, 'homePage'])->name('home');
Route::get('/courses', [HomePagesController::class, 'coursesPage'])->name('courses');
Route::get('/lessons', [HomePagesController::class, 'lessonsPage'])->name('lessons');
Route::get('/about', [HomePagesController::class, 'aboutPage'])->name('about');
Route::get('/contact', [HomePagesController::class, 'contactPage'])->name('contact');

// ///////////// Cart Routes //////////////
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/update', [CartController::class, 'updateCart'])->name('cart.update');
Route::post('/cart/remove', [CartController::class, 'removeFromCart'])->name('cart.remove');
Route::post('/cart/apply-coupon', [CartController::class, 'applyCoupon'])->name('cart.apply-coupon');

// Include Admin Routes
require __DIR__ . '/admin.php';

// Include Student Routes
require __DIR__ . '/student.php';

// Auth Routes
require __DIR__ . '/auth.php';

