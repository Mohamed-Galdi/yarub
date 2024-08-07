<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\LessonController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Admin\TestController;
use App\Http\Controllers\Admin\CertificateController;
use App\Http\Controllers\Admin\ConversationController;
use App\Http\Controllers\Admin\GuestMessagesController;
use App\Http\Controllers\Admin\PagesController;
use App\Http\Controllers\Admin\TestAttemptController;
use Illuminate\Support\Facades\Route;

// ///////////// Admin Routes //////////////
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin-dashboard')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    // Admin account
    Route::get('/account', [AdminDashboardController::class, 'show'])->name('admin.account');
    Route::post('/account', [AdminDashboardController::class, 'updateMain'])->name('admin.account.update-main');
    Route::post('/account/update-main-password', [AdminDashboardController::class, 'updateMainPassword'])->name('admin.account.update-main-password');
    Route::post('/account/create', [AdminDashboardController::class, 'createAdmin'])->name('admin.account.create');
    Route::put('/account/update-admin/{admin_id}', [AdminDashboardController::class, 'updateAdmin'])->name('admin.account.update-admin');
    Route::delete('/account/delete-admin/{admin_id}', [AdminDashboardController::class, 'deleteAdmin'])->name('admin.account.delete-admin');

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



    // Lessons
    Route::get('/lessons', [LessonController::class, 'index'])->name('admin.lessons');
    Route::get('/lessons/create', [LessonController::class, 'create'])->name('admin.lessons.create');
    Route::post('/lessons', [LessonController::class, 'store'])->name('admin.lessons.store');
    Route::get('/lessons/edit/{id}', [LessonController::class, 'edit'])->name('admin.lessons.edit');
    Route::get('/lessons/view/{id}', [LessonController::class, 'show'])->name('admin.lessons.view');
    Route::put('/lessons/{id}', [LessonController::class, 'update'])->name('admin.lessons.update');
    Route::delete('/lessons/content/{id}', [LessonController::class, 'deleteContent'])->name('lessons.content.delete');
    // detach lesson from student
    Route::delete('/lessons/detach/{lesson_id}/{student_id}', [LessonController::class, 'detach'])->name('admin.lessons.detach');

    //  upload video
    Route::post('/upload-video', [CourseController::class, 'uploadVideo'])->name('upload.video');

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
    Route::post('/support/conversations/{conversation}/reply', [ConversationController::class, 'reply'])->name('admin.conversations.send-reply');
    Route::get('/support/conversations/create', [ConversationController::class, 'create'])->name('admin.conversations.create');
    Route::post('/support/conversations', [ConversationController::class, 'store'])->name('admin.conversations.store');

    // Messages
    Route::get('/guest-messages-from-about', [GuestMessagesController::class, 'messages_from_about'])->name('messages_from_about');
    Route::get('/guest-messages-from-contact', [GuestMessagesController::class, 'messages_from_contact'])->name('messages_from_contact');

    // Pages
    Route::get('/pages', [PagesController::class, 'index'])->name('admin.pages');

    Route::get('/pages/edit-home' , [PagesController::class, 'editHome'])->name('admin.pages.edit-home');
    Route::post('/pages/edit-home' , [PagesController::class, 'updateHome'])->name('admin.pages.update-home');
    Route::post('/pages/edit-review/{review_id}' , [PagesController::class, 'updateReview'])->name('admin.pages.update-review');

    Route::get('/pages/edit-about' , [PagesController::class, 'editAbout'])->name('admin.pages.edit-about');
    Route::post('/pages/edit-about' , [PagesController::class, 'updateAbout'])->name('admin.pages.update-about');
    Route::post('/pages/add-partner' , [PagesController::class, 'addPartner'])->name('admin.pages.add-partner');
    Route::put('/pages/edit-partner/{partner_id}' , [PagesController::class, 'updatePartner'])->name('admin.pages.update-partner');
    Route::delete('/pages/delete-partner/{partner_id}', [PagesController::class, 'deletePartner'])->name('admin.pages.delete-partner');
    Route::post('/pages/add-faq' , [PagesController::class, 'addFaq'])->name('admin.pages.add-faq');
    Route::put('/pages/edit-faq/{faq_id}' , [PagesController::class, 'updateFaq'])->name('admin.pages.update-faq');
    Route::delete('/pages/delete-faq/{faq_id}', [PagesController::class, 'deleteFaq'])->name('admin.pages.delete-faq');

    Route::get('/pages/edit-contact' , [PagesController::class, 'editContact'])->name('admin.pages.edit-contact');
    Route::post('/pages/edit-contact' , [PagesController::class, 'updateContact'])->name('admin.pages.update-contact');
    



    Route::get('/payments', function () {
        return view('admin.payments.payments');
    })->name('admin.payments');
});

Route::post('/guest-messages', [GuestMessagesController::class, 'store'])->name('admin.guest_messages.store');