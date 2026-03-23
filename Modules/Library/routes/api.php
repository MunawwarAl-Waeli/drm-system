<?php

use Illuminate\Support\Facades\Route;
use Modules\Library\App\Http\Controllers\ProtectedDocumentController;
use Modules\Library\App\Http\Controllers\PublicationController;
 // تأكد من إضافة هذا السطر

Route::prefix("publisher_panel")->group(function () {

    Route::middleware(['auth:publisher_api', 'ability:panel-access'])->group(function () {

        // =========================================================
        // 2. وحدة المكتبة (Library Management - Publications)
        // =========================================================
        Route::prefix('publications')->group(function () {
            // عرض جميع المنشورات للناشر (الصورة الثالثة)
            Route::get('/', [PublicationController::class, 'index']);

            // الإجراءات الجماعية على المنشورات (With all checked)
            Route::post('/batch-action', [PublicationController::class, 'batchAction']);

            // التعديل المفرد على منشور (تعديل الوصف والـ Obey)
            Route::put('/{publication}', [PublicationController::class, 'update']);
        });

        // =========================================================
        // 3. وحدة المستندات المحمية (Protected Documents)
        // =========================================================
        Route::prefix('documents')->group(function () {

            // عرض جميع المستندات المحمية للناشر (الصورة التي فيها Suspend و Activate)
            Route::get('/', [ProtectedDocumentController::class, 'index']);

            // الإجراءات الجماعية والفردية على المستندات (With all checked)
            Route::post('/batch-action', [ProtectedDocumentController::class, 'batchAction']);

            // عرض تفاصيل مستند محدد (الصورة الأخيرة التي ناقشناها الخاصة بـ Document Controls)
            Route::get('/{document}', [ProtectedDocumentController::class, 'show']);

            // تحديث ملاحظات المستند (الزر الأزرق Save في أسفل شاشة التفاصيل)
            Route::put('/{document}/notes', [ProtectedDocumentController::class, 'updateNotes']);

        });

    });
});
