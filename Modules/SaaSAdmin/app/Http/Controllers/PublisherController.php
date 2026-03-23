<?php

namespace Modules\SaaSAdmin\Http\Controllers;

use Illuminate\Routing\Controller;
use Modules\SaaSAdmin\Http\Requests\StorePublisherAndLicenseRequest;
use Modules\SaaSAdmin\Actions\CreatePublisherAndLicenseAction;
use Modules\SaaSAdmin\Transformers\PublisherLicenseResource;

class PublisherController extends Controller
{
    /**
     * تسجيل ناشر جديد وإصدار ملف الرخصة LZPK له
     */
    public function store(StorePublisherAndLicenseRequest $request, CreatePublisherAndLicenseAction $action)
    {
        // 1. تحديد من هو الموظف الذي قام بالعملية (لنفترض حالياً الموظف رقم 1 كمثال)
        $adminId = auth()->id() ?? 1;

        // 2. تجميع الاستثناءات (Overrides) إن وجدت في الطلب
        $customQuotas = $request->only([
            'custom_max_documents',
            'custom_max_file_size_mb',
            'custom_max_total_storage_mb',
            'custom_batch_size',
            'custom_devices_allowed'
        ]);

        // 3. استدعاء الـ Action لتنفيذ الدورة الكاملة (داتابيز + تشفير)
        $result = $action->execute(
            $request->only(['name', 'email', 'company']), // بيانات الناشر
            $request->package_id,                         // رقم الباقة
            $adminId,                                     // الموظف
            $customQuotas                                 // الاستثناءات
        );

        // 4. تنسيق الرد للواجهة الأمامية
        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الناشر بنجاح وإصدار ملف الرخصة.',
            'data' => [
                'publisher_name' => $result['publisher']->name,
                'license_details' => new PublisherLicenseResource($result['license']),

                // نرسل الملف الثنائي كـ Base64 لتقوم الواجهة الأمامية بتحميله كملف .lzpk
                'file_name' => 'publisher_' . $result['publisher']->id . '_license.lzpk',
                'file_base64' => base64_encode($result['binary_file']),
            ]
        ], 201);
    }
}
