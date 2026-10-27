<?php

use App\Http\Controllers\Admin\VideoController;
use App\Http\Controllers\Admin\VideoLibraryController;
use App\Http\Controllers\VideoMediaController;
use App\Http\Middleware\VideoLibraryAccess;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified', 'student'])->get('/student-dashboard/videos', [\App\Http\Controllers\Student\VideoLibraryController::class, 'index'])->name('student.videos.index');
Route::middleware(VideoLibraryAccess::class.':student')->get('/student-dashboard/videos/catalog', [\App\Http\Controllers\Student\VideoLibraryController::class, 'results'])->name('student.videos.catalog');
Route::middleware(VideoLibraryAccess::class.':student')->get('/student-dashboard/videos/{video}', [\App\Http\Controllers\Student\VideoLibraryController::class, 'show'])->whereNumber('video')->name('student.videos.show');
Route::get('/videos', [\App\Http\Controllers\VideoShowcaseController::class, 'index'])->name('videos');

// Pages follow the existing admin guard; media/JSON retain error responses without redirects.
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin-dashboard/videos')->name('admin.videos.')->group(function () {
    Route::get('/', [VideoLibraryController::class, 'index'])->name('index');
    Route::get('/create', [VideoLibraryController::class, 'create'])->name('create');
    Route::get('/{video}/edit', [VideoLibraryController::class, 'edit'])->whereNumber('video')->name('edit');
});

Route::middleware(VideoLibraryAccess::class.':admin')->prefix('admin-dashboard/videos')->name('admin.videos.')->group(function () {
    Route::post('/', [VideoController::class, 'store'])->name('store');
    Route::put('/public-example', [VideoController::class, 'example'])->name('public-example');
    Route::get('/operations/{key}', [VideoController::class, 'operation'])->whereUuid('key')->name('operation');
    Route::get('/{video}', [VideoController::class, 'show'])->whereNumber('video')->name('show');
    Route::patch('/{video}', [VideoController::class, 'update'])->whereNumber('video')->name('update');
    Route::post('/{video}/media', [VideoController::class, 'replace'])->whereNumber('video')->name('replace');
    Route::patch('/{video}/visibility', [VideoController::class, 'visibility'])->whereNumber('video')->name('visibility');
    Route::delete('/{video}', [VideoController::class, 'destroy'])->whereNumber('video')->name('destroy');
    Route::get('/{video}/preview/{asset}', [VideoMediaController::class, 'admin'])->whereNumber('video')->whereIn('asset', ['media', 'cover'])->name('preview');
});

Route::middleware(VideoLibraryAccess::class.':student')->get('/student-dashboard/videos/{video}/{asset}', [VideoMediaController::class, 'student'])
    ->whereNumber('video')->whereIn('asset', ['media', 'cover'])->name('student.videos.asset');

Route::middleware(VideoLibraryAccess::class)->get('/videos/example/{asset}', [VideoMediaController::class, 'example'])
    ->whereIn('asset', ['media', 'cover'])->name('videos.example.asset');
