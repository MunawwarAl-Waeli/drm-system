<?php

use Illuminate\Support\Facades\Route;
use Modules\CustomerManagement\Http\Controllers\CustomerAccountController;
use Modules\CustomerManagement\Http\Controllers\CustomerDocumentsController;
use Modules\CustomerManagement\Http\Controllers\CustomerPublicationsController;
use Modules\CustomerManagement\Http\Controllers\DeviceController;
use Modules\CustomerManagement\Http\Controllers\DocumentCustomersController;
use Modules\CustomerManagement\Http\Controllers\LicenseController;
use Modules\CustomerManagement\Http\Controllers\PublicationAccessController;



Route::prefix("publisher_panel")->group(function () {

    Route::middleware(['auth:publisher_api', 'ability:panel-access'])->group(function () {

        Route::post('/licenses/individual', [LicenseController::class, 'storeIndividual']);

        // رابط الرخص الجماعية (الكروت)
        Route::post('/licenses/vouchers', [LicenseController::class, 'storeGroup']);
    });
});


// publisher_panel/licenses/vouchers
// مجموعة الروابط الخاصة بوصول العملاء للمنشورات
Route::prefix('publications/{publication}')->group(function () {

    // 1. رابط جلب قائمة العملاء (يدعم البحث، الفلترة، والترتيب)
    // نوع الطلب: GET
    Route::get('/access', [PublicationAccessController::class, 'index']);

    // 2. رابط تنفيذ الإجراءات الجماعية (منح/سحب الصلاحيات)
    // نوع الطلب: POST
    Route::post('/access/bulk', [PublicationAccessController::class, 'bulkAction']);

});


Route::prefix('documents/{document}')->group(function () {

    // 1. رابط جلب قائمة العملاء للمستند (يدعم البحث، الفلترة، والترتيب)
    // نوع الطلب: GET
    Route::get('/access', [DocumentCustomersController::class, 'index']);

    // 2. رابط تنفيذ الإجراءات الجماعية للمستند (منح/سحب الصلاحيات)
    // نوع الطلب: POST
    Route::post('/access/bulk', [DocumentCustomersController::class, 'bulkAction']);

});

Route::middleware('api')->group(function () {
    // عرض قائمة العملاء
    Route::get('/customers', [CustomerAccountController::class, 'index']);

    // تنفيذ الإجراءات على العملاء المحددين
    Route::post('/customers/bulk-action', [CustomerAccountController::class, 'bulkAction']);

    // جلب تفاصيل عميل محدد
    Route::get('/customers/{id}', [CustomerAccountController::class, 'show']);

    // تحديث بيانات عميل محدد (بما فيها تقييد الموقع)
    Route::put('/customers/{id}', [CustomerAccountController::class, 'update']);
    // جلب أجهزة العميل (رقم الرخصة هو المتغير)
});


Route::prefix('licenses/{license}/devices')->group(function () {
    // عرض أجهزة هذا العميل
    Route::get('/', [DeviceController::class, 'index']);

    // الإجراء الجماعي لأجهزة هذا العميل (الآيدي في الرابط، وباقي البيانات في الـ Body)
    Route::post('/bulk-action', [DeviceController::class, 'bulkAction']);
});


// تجميع الروابط تحت بادئة (Prefix) واضحة
Route::prefix('customer-management')->group(function () {

    // روابط إدارة وصول العميل (الرخصة) للمنشورات
    Route::prefix('licenses/{license}/publications')->group(function () {
        Route::get('/', [CustomerPublicationsController::class, 'index']);
        Route::post('/bulk-action', [CustomerPublicationsController::class, 'bulkAction']);
    });

    // روابط إدارة وصول العميل (الرخصة) للمستندات المباشرة
    Route::prefix('licenses/{license}/documents')->group(function () {
        Route::get('/', [CustomerDocumentsController::class, 'index']);
        Route::post('/bulk-action', [CustomerDocumentsController::class, 'bulkAction']);
    });

});

Route::middleware(['auth:publisher_api', 'ability:panel-access'])->group(function () {
    // ... بقية المسارات

    // رابط تحميل ملف الرخصة للعميل (فردي أو كروت)
    Route::get('/licenses/{license}/download', [LicenseController::class, 'downloadFile']);
});
