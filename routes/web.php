<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\StudentController;
use App\Models\Course;

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
    Route::delete('/students/delete/{student_id}', [StudentController::class, 'destroy'])->name('admin.students.delete');
    // list of deleted students
    Route::get('/students/deleted', [StudentController::class, 'deleted'])->name('admin.students.deleted');
    // restore deleted student
    Route::delete('/students/restore/{student_id}', [StudentController::class, 'restore'])->name('admin.students.restore');

    // Courses
    Route::get('/courses', [CourseController::class, 'index'])->name('admin.courses');
    Route::get('/courses/create', [CourseController::class, 'create'])->name('admin.courses.create');
    Route::post('/courses', [CourseController::class, 'store'])->name('admin.courses.store');
    Route::get('/courses/{id}' , [CourseController::class, 'show'])->name('admin.courses.view');
    Route::get('/courses/edit/{id}', [CourseController::class, 'edit'])->name('admin.courses.edit');

    Route::put('/courses/{id}', [CourseController::class, 'update'])->name('admin.courses.update');
    Route::delete('/courses/content/{id}', [CourseController::class, 'deleteContent'])->name('courses.content.delete');
    // detach course from student
    Route::delete('/courses/detach/{course_id}/{student_id}', [CourseController::class, 'detach'])->name('admin.courses.detach');
    // detach lesson from student
    Route::delete('/lessons/detach/{lesson_id}/{student_id}', [LessonController::class, 'detach'])->name('admin.lessons.detach');
    

    // Lessons
    Route::get('/lessons', [LessonController::class, 'index'])->name('admin.lessons');
    Route::get('/lessons/create', [LessonController::class, 'create'])->name('admin.lessons.create');
    Route::post('/lessons', [LessonController::class, 'store'])->name('admin.lessons.store');
    Route::get('/lessons/edit/{id}', [LessonController::class, 'edit'])->name('admin.lessons.edit');
    Route::get('/lessons/view/{id}', [LessonController::class, 'show'])->name('admin.lessons.view');
    Route::put('/lessons/{id}', [LessonController::class, 'update'])->name('admin.lessons.update');

    Route::get('/tests', function () { return view('admin.tests'); })->name('admin.tests');
    Route::get('/certificates', function () { return view('admin.certificates'); })->name('admin.certificates');
    Route::get('/payments', function () { return view('admin.payments'); })->name('admin.payments');
    Route::get('/support', function () { return view('admin.support'); })->name('admin.support');
    Route::get('/coupons', function () { return view('admin.coupons'); })->name('admin.coupons');
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

Route::post('/upload-video', [CourseController::class, 'uploadVideo'])->name('upload.video');
Route::delete('/courses/content/{id}', [CourseController::class, 'deleteContent'])->name('courses.content.delete');
Route::delete('/lessons/content/{id}', [LessonController::class, 'deleteContent'])->name('lessons.content.delete');
