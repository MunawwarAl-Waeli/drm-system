<?php

namespace Modules\PublisherWorkspace\Http\Controllers\Publisher;

use App\Http\Controllers\Controller; // 👈 استخدمنا الكنترولر الأساسي
use Illuminate\Support\Facades\Storage;
use Modules\PublisherWorkspace\Http\Requests\RegisterPublisherRequest;
use Modules\PublisherWorkspace\Actions\CreatePublisherAndLicenseAction;
use Modules\PublisherWorkspace\Models\PublisherLicense;
use Modules\PublisherWorkspace\Transformers\PublisherResource;
use Modules\SaaSAdmin\Models\Package;
use App\Traits\ApiResponseTrait; // 👈 1. استدعاء صانع الغلاف الموحد

class PublisherRegistrationController extends Controller
{
    use ApiResponseTrait; // 👈 2. تفعيله

    public function register(RegisterPublisherRequest $request, CreatePublisherAndLicenseAction $action)
    {
        // 1. تحديد الباقة الافتراضية
        $package = Package::where('is_default_registration', true)->firstOrFail();

        // 2. تنفيذ الأكشن
        $result = $action->execute(
            $request->validated(),
            $package->id,
            auth()->id() // سيعطي null لو كان تسجيلاً ذاتياً
        );

        // 3. تجهيز البيانات (استخراجها من الـ Resource لتكون مصفوفة صافية)
        $payloadData = [
            'publisher' => (new PublisherResource($result['publisher']))->resolve(),
        ];

        // 4. الرد الموحد (201 Created)
        return $this->sendResponse(
            true,
            'publisher_registered',
            'تم التسجيل وإصدار الرخصة بنجاح.',
            $payloadData,
            201
        );
    }

    /**
     * دالة تحميل الملف (ترجع ملفاً عند النجاح، و JSON موحد عند الفشل)
     */
    public function downloadLicense(PublisherLicense $license)
    {
        // 1. التأكد أن الناشر يملك هذه الرخصة فعلاً (استبدلنا abort برد موحد)
        if ($license->publisher_id !== auth()->id()) {
            return $this->sendResponse(
                false,
                'access_denied',
                'غير مصرح لك بتحميل هذا الملف.',
                null,
                403
            );
        }

        // 2. التأكد من وجود الملف فعلياً في السيرفر
        if (!Storage::disk('local')->exists($license->lic_file_path)) {
            return $this->sendResponse(
                false,
                'file_not_found',
                'عفواً، ملف الرخصة غير موجود على الخادم.',
                null,
                404
            );
        }

        // 3. النجاح: إرجاع الملف للتحميل المباشر (لا نستخدم الغلاف هنا لأن الرد هو ملف)
        return Storage::disk('local')->download($license->lic_file_path, "license.lzpk");
    }
}
