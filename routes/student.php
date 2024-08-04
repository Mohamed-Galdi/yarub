<?php


use Illuminate\Support\Facades\Route;


// ///////////// Student Routes //////////////
Route::middleware(['auth', 'verified', 'student'])->group(function () {
    Route::get('/student-dashboard', function () {
        return view('student.dashboard');
    })->name('student.dashboard');
    // Route::get('/student-courses', function () { return view('student.courses'); })->name('student.courses');
    // Route::get('/student-lessons', function () { return view('student.lessons'); })->name('student.lessons');
});
