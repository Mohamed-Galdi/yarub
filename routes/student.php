<?php

use App\Http\Controllers\Student\CoursesController;
use App\Http\Controllers\Student\LessonsController;
use App\Http\Controllers\Student\TestsController;
use Illuminate\Support\Facades\Route;


// ///////////// Student Routes //////////////
Route::middleware(['auth', 'verified', 'student'])->prefix('student-dashboard')->group(function () {

    // redirect / to /courses
    Route::get('/', [CoursesController::class, 'index'])->name('student.dashboard');

    // /////////// Account Routes ////////////
    Route::get('/account', function () { return view('student.account.index');})->name('student.account.index');

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

    // /////////// Certificates Routes ////////////
    Route::get('/certificates', function () { return view('student.certificates.index');})->name('student.certificates.index');

    // /////////// Support Routes ////////////
    Route::get('/support', function () { return view('student.support.index');})->name('student.support.index');

});
