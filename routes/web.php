<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\StudentController;

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

    // students
    Route::get('/students', [StudentController::class, 'index'])->name('admin.students');
    Route::get('/students/view/{id}', [StudentController::class, 'show'])->name('admin.view_student');

    // Courses
    Route::get('/courses', [CourseController::class, 'index'])->name('admin.courses');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('admin.courses.create');
    Route::get('/courses/edit/{id}', [CourseController::class, 'edit'])->name('admin.courses.edit');

    // Lessons
    Route::get('/lessons', [LessonController::class, 'index'])->name('admin.lessons');
    Route::get('/lessons/create', [LessonController::class, 'create'])->name('admin.lessons.create');
    Route::get('/lessons/edit/{id}', [LessonController::class, 'edit'])->name('admin.lessons.edit');

    Route::get('/tests', function () { return view('admin.tests'); })->name('admin.tests');
    Route::get('/certificates', function () { return view('admin.certificates'); })->name('admin.certificates');
    Route::get('/payments', function () { return view('admin.payments'); })->name('admin.payments');
    Route::get('/support', function () { return view('admin.support'); })->name('admin.support');
});


// ///////////// Student Routes //////////////
Route::middleware(['auth', 'verified', 'student'])->group(function () {
    Route::get('/student-dashboard', function () { return view('student.dashboard'); })->name('student.dashboard');
    // Route::get('/student-courses', function () { return view('student.courses'); })->name('student.courses');
    // Route::get('/student-lessons', function () { return view('student.lessons'); })->name('student.lessons');
});



// ///////////// Home Routes //////////////
Route::get('/', function () { return view('home.home'); })->name('home');
Route::get('/courses', function () { return view('home.courses.courses_list'); })->name('courses');
Route::get('/lessons', function () { return view('home.lessons.lessons_list'); })->name('lessons');
Route::get('/about', function () { return view('home.about'); })->name('about');
Route::get('/contact', function () { return view('home.contact'); })->name('contact');


// ///////////// Auth Routes //////////////
require __DIR__ . '/auth.php';
