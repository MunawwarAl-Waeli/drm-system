<?php

use Illuminate\Support\Facades\Route;

use Modules\ReaderApp\Http\Controllers\ReaderActivationController;
use Modules\ReaderApp\Http\Controllers\ReaderAuthController;
use Modules\ReaderApp\Http\Controllers\ReaderAppController;
use Modules\ReaderApp\Http\Controllers\ReaderSyncController;
use Modules\ReaderApp\Http\Controllers\ReaderVerificationController;


Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('readerapps', ReaderAppController::class)->names('readerapp');
});


Route::prefix('reader-app')->group(function () {
    // مسار إنشاء الحساب
    Route::post('/register', [ReaderAuthController::class, 'register']);
    // مسار تسجيل الدخول المخصص
    Route::post('/login', [ReaderAuthController::class, 'login']);

// ==========================================
// مسارات محمية بتوكن القارئ
// ==========================================
    Route::middleware(['auth:reader_api',])->group(function () {

        // // مسار إلغاء تنشيط الجهاز (تسجيل الخروج من البرنامج)
        Route::post('/activate', [ReaderActivationController::class, 'activate']);
        Route::post('/verify', [ReaderVerificationController::class, 'verify']);
        Route::post('/ping', [ReaderActivationController::class, 'ping']);
        Route::post('/syncContent', [ReaderSyncController::class, 'syncContent']);

    });

});

