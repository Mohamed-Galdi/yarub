<?php

use App\Http\Controllers\Student\CoursesController;
use Illuminate\Support\Facades\Route;


// ///////////// Student Routes //////////////
Route::middleware(['auth', 'verified', 'student'])->group(function () {

    // /////////// Account Routes ////////////
    Route::get('/student-dashboard/account', function () { return view('student.account.index');})->name('student.account.index');

    // /////////// Courses Routes ////////////
    Route::get('/student-dashboard/courses', [CoursesController::class, 'index'])->name('student.courses.index');




    
    // /////////// Lessons Routes ////////////
    Route::get('/student-dashboard/lessons', function () { return view('student.lessons.index');})->name('student.lessons.index');

    // /////////// Tests Routes ////////////
    Route::get('/student-dashboard/tests', function () { return view('student.tests.index');})->name('student.tests.index');

    // /////////// Certificates Routes ////////////
    Route::get('/student-dashboard/certificates', function () { return view('student.certificates.index');})->name('student.certificates.index');

    // /////////// Support Routes ////////////
    Route::get('/student-dashboard/support', function () { return view('student.support.index');})->name('student.support.index');

});
