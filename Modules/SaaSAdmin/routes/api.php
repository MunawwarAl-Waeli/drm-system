<?php

use Illuminate\Support\Facades\Route;
use Modules\SaaSAdmin\Http\Controllers\PublisherController;
use Modules\SaaSAdmin\Http\Controllers\WriterApiController;
use Modules\SaaSAdmin\Http\Controllers\SaaSAdminController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('saasadmins', SaaSAdminController::class)->names('saasadmin');
});


Route::prefix('saas')->group(function() {

    // مسار إضافة ناشر جديد وإصدار رخصته
    Route::post('/publishers', [PublisherController::class, 'store']);

    Route::post('/writer/verify', [WriterApiController::class, 'verify']);

});

