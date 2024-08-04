<?php

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



// Include Admin Routes
require __DIR__ . '/admin.php';

// Include Student Routes
require __DIR__ . '/student.php';

// Auth Routes
require __DIR__ . '/auth.php';

