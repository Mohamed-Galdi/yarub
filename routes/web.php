<?php

use App\Http\Controllers\ProfileController;
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

Route::get('/', function () { return view('home.home');})->name('home');

Route::get('/about', function () { return view('home.about');})->name('about');

Route::get('/courses', function () { return view('courses.courses_list');})->name('courses');

Route::get('/lessons', function () { return view('lessons.lessons_list');})->name('lessons');

Route::get('/contact', function () { return view('home.contact');})->name('contact'); 


// ///////////// Student Routes //////////////
Route::get('/student-dashboard', function () { return view('student.dashboard');})->middleware(['auth', 'verified'])->name('student.dashboard');
Route::get('/student-courses', function () { return view('student.courses');})->middleware(['auth', 'verified'])->name('student.courses');



Route::get('/admin-dashboard', function () { return view('admin.dashboard');})->middleware(['auth', 'verified'])->name('admin.dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
