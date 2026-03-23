<?php

namespace Modules\ReaderApp\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ReaderApp\Http\Requests\RegisterReaderRequest;
use Modules\ReaderApp\Http\Requests\LoginReaderRequest;
use Modules\ReaderApp\Services\ReaderAuthService;
use App\Traits\ApiResponseTrait; // 👈 1. استدعاء صانع الغلاف الموحد

class ReaderAuthController extends Controller
{
    use ApiResponseTrait; // 👈 2. تفعيل التريت لتوحيد الردود

    protected $authService;

    public function __construct(ReaderAuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(RegisterReaderRequest $request)
    {
        $result = $this->authService->registerReader(
            $request->validated(),
            $request->ip()
        );

        // 3. تجهيز المحتوى الداخلي (Payload) بشكل نظيف
        $payloadData = [
            'user_id' => $result['reader']->id,
            'name' => $result['reader']->name,
            'email' => $result['reader']->email,
            'token' => $result['token'],
        ];

        // 4. استخدام الغلاف الموحد وإرسال كود الحالة 201 (تم الإنشاء)
        return $this->sendResponse(
            true,
            'register_success', // 👈 أضفنا الـ action ليكون موحداً مع بقية النظام
            'تم إنشاء الحساب وتسجيل الجهاز بنجاح.',
            $payloadData,
            201
        );
    }

    public function login(LoginReaderRequest $request)
    {
        $result = $this->authService->loginReader(
            $request->validated(),
            $request->ip()
        );

        // 3. تجهيز المحتوى الداخلي (Payload)
        $payloadData = [
            'user_id' => $result['reader']->id,
            'name' => $result['reader']->name,
            'email' => $result['reader']->email,
            'token' => $result['token'],
        ];

        // 4. استخدام الغلاف الموحد (الرد الافتراضي هنا 200)
        return $this->sendResponse(
            true,
            'login_success', // 👈 الـ action يخبر التطبيق بالتوجه للصفحة الرئيسية
            'تم تسجيل الدخول بنجاح.',
            $payloadData
        );
    }
}
