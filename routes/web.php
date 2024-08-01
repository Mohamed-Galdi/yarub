<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TestController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\TestAttemptController;
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
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');

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
    Route::get('/courses/{id}', [CourseController::class, 'show'])->name('admin.courses.view');
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

    // Tests
    Route::get('/tests', [TestController::class, 'index'])->name('admin.tests');
    Route::get('/tests/create', [TestController::class, 'create'])->name('admin.tests.create');
    Route::post('/tests', [TestController::class, 'store'])->name('admin.tests.store');
    Route::get('/tests/edit/{id}', [TestController::class, 'edit'])->name('admin.tests.edit');
    Route::put('/tests/{id}', [TestController::class, 'update'])->name('admin.tests.update');
    Route::get('/tests/view/{id}', [TestController::class, 'show'])->name('admin.tests.view');
    Route::get('/tests/test-attempts/view/{id}', [TestAttemptController::class, 'adminShow'])->name('admin.test_attempt');

    // Coupons
    Route::get('/coupons', [CouponController::class, 'index'])->name('admin.coupons');
    Route::get('/coupons/create', [CouponController::class, 'create'])->name('admin.coupons.create');
    Route::post('/coupons', [CouponController::class, 'store'])->name('admin.coupons.store');
    Route::get('/coupons/edit/{id}', [CouponController::class, 'edit'])->name('admin.coupons.edit');
    Route::put('/coupons/{id}', [CouponController::class, 'update'])->name('admin.coupons.update');

    // Certificates
    Route::get('/certificates', [CertificateController::class, 'index'])->name('admin.certificates');
    Route::get('/certificates/pdf', [CertificateController::class, 'pdf'])->name('admin.certificates.pdf');
    Route::get('/certificates/view/{id}', [CertificateController::class, 'show'])->name('admin.certificates.view');
    Route::get('/certificates/create', [CertificateController::class, 'create'])->name('admin.certificates.create');
    Route::post('/certificates', [CertificateController::class, 'store'])->name('admin.certificates.store');
    Route::get('/certificates/get-student-content', [CertificateController::class, 'getStudentContent'])->name('admin.certificates.get-student-content');
    Route::get('/certificates/download/{id}', [CertificateController::class, 'download'])->name('admin.certificates.download');
    
    // Support
    Route::get('/support', [ConversationController::class, 'index'])->name('admin.support');
    Route::get('/support/conversations', [ConversationController::class, 'index'])->name('admin.conversations.index');
    Route::get('/support/conversations/{conversation}/reply', [ConversationController::class, 'showReplyForm'])->name('admin.conversations.reply');
    Route::post(
        '/support/conversations/{conversation}/reply',
        [ConversationController::class, 'reply']
    )->name('admin.conversations.send-reply');
    Route::get('/support/conversations/create', [ConversationController::class, 'create'])->name('admin.conversations.create');
    Route::post('/support/conversations', [ConversationController::class, 'store'])->name('admin.conversations.store');



    Route::get('/payments', function () {
        return view('admin.payments');
    })->name('admin.payments');
});


// ///////////// Student Routes //////////////
Route::middleware(['auth', 'verified', 'student'])->group(function () {
    Route::get('/student-dashboard', function () {
        return view('student.dashboard');
    })->name('student.dashboard');
    // Route::get('/student-courses', function () { return view('student.courses'); })->name('student.courses');
    // Route::get('/student-lessons', function () { return view('student.lessons'); })->name('student.lessons');
});



// ///////////// Home Routes //////////////
Route::get('/', function () {
    return view('home.home');
})->name('home');
Route::get('/courses', function () {
    return view('home.courses.courses_list');
})->name('courses');
Route::get('/lessons', function () {
    return view('home.lessons.lessons_list');
})->name('lessons');
Route::get('/about', function () {
    return view('home.about');
})->name('about');
Route::get('/contact', function () {
    return view('home.contact');
})->name('contact');


// ///////////// Auth Routes //////////////
require __DIR__ . '/auth.php';

Route::post('/upload-video', [CourseController::class, 'uploadVideo'])->name('upload.video');
Route::delete('/courses/content/{id}', [CourseController::class, 'deleteContent'])->name('courses.content.delete');
Route::delete('/lessons/content/{id}', [LessonController::class, 'deleteContent'])->name('lessons.content.delete');
