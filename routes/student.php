<?php

use App\Http\Controllers\Student\CertificatesController;
use App\Http\Controllers\Student\CoursesController;
use App\Http\Controllers\Student\LessonsController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Student\SupportController;
use App\Http\Controllers\Student\TestsController;
use Illuminate\Support\Facades\Route;


// ///////////// Student Routes //////////////
Route::middleware(['auth', 'verified', 'student', 'log.visit'])->prefix('student-dashboard')->group(function () {

    // redirect / to /courses
    Route::get('/', [CoursesController::class, 'index'])->name('student.dashboard');


    // /////////// Courses Routes ////////////
    Route::get('/courses', [CoursesController::class, 'index'])->name('student.courses.index');
    Route::get('/courses/{course}', [CoursesController::class, 'show'])->name('student.courses.show');
    // course rating
    Route::post('/courses/{course_id}/rating', [CoursesController::class, 'rating'])->name('student.courses.rating');


    // /////////// Lessons Routes ////////////
    Route::get('/lessons', [LessonsController::class, 'index'])->name('student.lessons.index');
    Route::get('/lessons/{lesson}', [LessonsController::class, 'show'])->name('student.lessons.show');
    // lesson rating
    Route::post('/lessons/{lesson_id}/rating', [LessonsController::class, 'rating'])->name('student.lessons.rating');

    // /////////// Tests Routes ////////////
    Route::get('/tests', [TestsController::class, 'index'])->name('student.tests.index');
    Route::get('/tests/{test}', [TestsController::class, 'take'])->name('student.tests.take');
    Route::post('/tests/{test}/submit', [TestsController::class, 'submit'])->name('student.tests.submit');
    Route::get( '/tests/{test}/result', [TestsController::class, 'result'] )->name('student.tests.result');

    // /////////// Certificates Routes ////////////
    Route::get('/certificates', [CertificatesController::class, 'index'])->name('student.certificates.index');

    // /////////// Students Profile Routes ////////////
    Route::get('/profile', [ProfileController::class, 'edit'])->name('student.account.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update.password');

    // /////////// Support Routes ////////////
    Route::get('/support',[ SupportController::class , 'index'])->name('student.support.index');
    Route::get('/support/create', [SupportController::class, 'create'])->name('student.conversations.create');
    Route::get('/support/{conversation}/reply', [SupportController::class, 'showReplyForm'])->name('student.conversations.reply');
    Route::post('/support/{conversation}/reply', [SupportController::class, 'reply'])->name('student.conversations.send-reply');
    Route::get('/support/conversations/create', [SupportController::class, 'create'])->name('student.conversations.create');
    Route::post('/support/conversations', [SupportController::class, 'store'])->name('student.conversations.store');






});
