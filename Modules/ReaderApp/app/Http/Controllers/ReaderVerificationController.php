<?php

namespace Modules\ReaderApp\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ReaderApp\Http\Requests\ReaderVerificationRequest;
use Modules\ReaderApp\Services\ReaderVerificationService;
use App\Traits\ApiResponseTrait; // 👈 استدعاء الغلاف الموحد

class ReaderVerificationController extends Controller
{
    use ApiResponseTrait; // 👈 تفعيله هنا

    protected $verificationService;

    public function __construct(ReaderVerificationService $verificationService)
    {
        $this->verificationService = $verificationService;
    }

    public function verify(ReaderVerificationRequest $request)
    {
        $reader = $request->user();

        // 1. إرسال البيانات للـ Service لتفحصها وتقيمها
        $result = $this->verificationService->verifyDocument($reader, $request->validated());

        // 2. إرجاع الرد المغلف بالـ Trait
        return $this->sendResponse(
            $result['success'],
            $result['action'],
            $result['message'],
            $result['data']
        );
    }
}
