<?php

namespace Modules\PublisherWorkspace\Http\Controllers\Writer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Modules\PublisherWorkspace\Services\WriterActivationService;
use App\Traits\ApiResponseTrait;

class WriterActivationController extends Controller
{
    use ApiResponseTrait;

    protected $activationService;

    public function __construct(WriterActivationService $activationService)
    {
        $this->activationService = $activationService;
    }

    public function activate(Request $request)
    {
        // 1. التحقق اليدوي للحفاظ على الغلاف الموحد
        $validator = Validator::make($request->all(), [
            'publisher_id' => 'required|integer',
            'license_key' => 'required|string',
            'hardware_id' => 'required|string',
            'device_name' => 'nullable|string',
            'os_version' => 'nullable|string',
            'app_version' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'validation', $validator->errors()->first(), $validator->errors(), 422);
        }

        // 2. تنفيذ الخدمة
        $result = $this->activationService->activateDevice($request->all(), $request->ip());


        $payloadData = [
            'token' => $result['token'],
            'device_id' => $result['device_id'],
            'publisher' => [
                'name' => $result['publisher']->name,
                'company' => $result['publisher']->company
            ]
        ];

        return $this->sendResponse(true, 'activation', 'تم تفعيل البرنامج بنجاح.', $payloadData, 200);
    }

    public function ping(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'hardware_id' => 'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendResponse(false, 'validation', 'رقم الجهاز مفقود.', null, 422);
        }

        // تمرير بيانات المستخدم من التوكن
        $this->activationService->pingDevice($request->user(), $request->hardware_id, $request->ip());

        // الرد بالاستمرار
        return $this->sendResponse(true, 'continue', 'الاتصال سليم.', null, 200);
    }

    public function deactivate(Request $request)
    {
        // إلغاء تنشيط الجهاز (تسجيل الخروج)
        $request->user()->currentAccessToken()->delete();

        return $this->sendResponse(true, 'logout', 'تم تسجيل الخروج وإلغاء تنشيط الجهاز.', null, 200);
    }
}
