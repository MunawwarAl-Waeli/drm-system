<?php

use Illuminate\Support\Facades\Route;

// Controllers
use Modules\PublisherWorkspace\Http\Controllers\Publisher\PublisherPanelAuthController;
use Modules\PublisherWorkspace\Http\Controllers\Publisher\PublisherRegistrationController;
use Modules\PublisherWorkspace\Http\Controllers\Writer\WriterActivationController;
use Modules\PublisherWorkspace\Http\Controllers\Writer\WriterContentController;

// Middleware
use Modules\PublisherWorkspace\Http\Middleware\CheckPublisherStatus;

/*
|--------------------------------------------------------------------------
| 1. مسارات التسجيل العامة (Public)
|--------------------------------------------------------------------------
| هذه المسارات مفتوحة للجميع ولا تتطلب أي صلاحيات.
*/
Route::prefix('publisher')->group(function () {
    Route::post('/register', [PublisherRegistrationController::class, 'register']);
});


/*
|--------------------------------------------------------------------------
| 2. مسارات لوحة تحكم الويب (Web Panel)
|--------------------------------------------------------------------------
| المسارات الخاصة بإدارة الناشر من المتصفح.
*/
Route::prefix('panel')->group(function () {

    // مسارات عامة للوحة
    Route::post('/login', [PublisherPanelAuthController::class, 'login']);

    // مسارات محمية للوحة (يجب توفر: توكن + صلاحية اللوحة + حساب نشط)
    Route::middleware([
        'auth:publisher_api',
        'ability:panel-access',
        CheckPublisherStatus::class
    ])->group(function () {

        Route::post('/logout', [PublisherPanelAuthController::class, 'logout']);

    });
});


/*
|--------------------------------------------------------------------------
| 3. مسارات برنامج الكاتب (Writer Desktop App)
|--------------------------------------------------------------------------
| المسارات التي يكلمها برنامج الـ C# من جهاز الكمبيوتر.
*/
Route::prefix('writer')->group(function () {

    // --- أ. مسارات المصادقة (لا تحتاج توكن للوصول إليها) ---
    Route::post('/activate', [WriterActivationController::class, 'activate']);
    Route::post('/deactivate', [WriterActivationController::class, 'deactivate']);

    // --- ب. مسارات العمل المحمية (تتطلب توكن + صلاحية الكاتب) ---
    Route::middleware([
        'auth:publisher_api',
        'ability:writer-access'
    ])->group(function () {

        // 1. جلب البيانات العادية
        Route::get('/publications', [WriterContentController::class, 'getPublications']);

        // 2. مسارات تتطلب توقيع رقمي وتشفير (Middleware إضافي: writer.signature)
        Route::post('/upload/file', [WriterContentController::class, 'sync']);
        Route::middleware(['writer.signature'])->group(function () {
        });

        // 3. مسارات تتطلب التحقق من حالة الحساب إن كان نشطاً (Middleware إضافي: CheckPublisherStatus)
        Route::middleware([CheckPublisherStatus::class])->group(function () {
            Route::post('/ping', [WriterActivationController::class, 'ping']);
        });

    });
});
