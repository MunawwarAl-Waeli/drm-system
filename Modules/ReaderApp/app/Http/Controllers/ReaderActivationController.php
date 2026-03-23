<?php

namespace Modules\ReaderApp\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\ReaderApp\Http\Requests\ReaderActivationRequest;
use Modules\ReaderApp\Services\LicenseContentService;
use Modules\ReaderApp\Services\ReaderActivationService;
use App\Traits\ApiResponseTrait; // 👈 استدعاء صانع الغلاف الموحد

class ReaderActivationController extends Controller
{
    use ApiResponseTrait; // 👈 تفعيل الغلاف الموحد

    protected $activationService;
    protected $contentService;

    public function __construct(
        ReaderActivationService $activationService,
        LicenseContentService $contentService
    ) {
        $this->activationService = $activationService;
        $this->contentService = $contentService;
    }

    public function activate(ReaderActivationRequest $request)
    {
        $reader = $request->user();

        // 1. تفعيل الرخصة عبر الخدمة
        $activeLicense = $this->activationService->activate($reader, $request->validated(), $request->ip());

        // 2. جلب المحتوى كـ Resource
        $payloadResource = $this->contentService->getLicensePayload($activeLicense);

        // 3. الرد الموحد والمغلف
        return $this->sendResponse(
            true,
            'activation_success',
            'تم تفعيل الرخصة وجلب المحتوى بنجاح.',
            $payloadResource // سيتم وضعه تلقائياً داخل حقل data
        );
    }

    public function ping(Request $request)
    {
        $request->validate(['hardware_id' => 'required|string']);

        // نقلنا اللوجيك إلى السيرفس لتنظيف الكنترولر
        $result = $this->activationService->pingDevice($request->user(), $request->hardware_id, $request->ip());

        if (!$result['success']) {
            return $this->sendResponse(false, $result['action'], $result['message'], null, 403);
        }

        return $this->sendResponse(true, $result['action'], $result['message']);
    }
}
