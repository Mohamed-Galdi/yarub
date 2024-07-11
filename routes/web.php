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

// ///////////// Admin Routes //////////////
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin-dashboard')->group(function () {
    Route::get('/', function () { return view('admin.dashboard'); })->name('admin.dashboard');
    Route::get('/students', function () { return view('admin.students'); })->name('admin.students');
    Route::get('/courses', function () { return view('admin.courses'); })->name('admin.courses');
    Route::get('/lessons', function () { return view('admin.lessons'); })->name('admin.lessons');
    Route::get('/tests', function () { return view('admin.tests'); })->name('admin.tests');
    Route::get('/certificates', function () { return view('admin.certificates'); })->name('admin.certificates');
    Route::get('/payments', function () { return view('admin.payments'); })->name('admin.payments');
    Route::get('/support', function () { return view('admin.support'); })->name('admin.support');
});


// ///////////// Student Routes //////////////
Route::middleware(['auth', 'verified', 'student'])->group(function () {
    Route::get('/student-dashboard', function () { return view('student.dashboard'); })->name('student.dashboard');
    Route::get('/student-courses', function () { return view('student.courses'); })->name('student.courses');
    Route::get('/student-lessons', function () { return view('student.lessons'); })->name('student.lessons');
});



// ///////////// Home Routes //////////////
Route::get('/', function () { return view('home.home'); })->name('home');
Route::get('/about', function () { return view('home.about'); })->name('about');
Route::get('/courses', function () { return view('courses.courses_list'); })->name('courses');
Route::get('/lessons', function () { return view('lessons.lessons_list'); })->name('lessons');
Route::get('/contact', function () { return view('home.contact'); })->name('contact');


// ///////////// Auth Routes //////////////
require __DIR__ . '/auth.php';
