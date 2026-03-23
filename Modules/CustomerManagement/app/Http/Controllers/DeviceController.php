<?php
namespace Modules\CustomerManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\CustomerManagement\Services\DeviceService;
use Modules\CustomerManagement\Transformers\DeviceResource;

class DeviceController extends Controller
{
    protected $deviceService;

    public function __construct(DeviceService $deviceService)
    {
        $this->deviceService = $deviceService;
    }

    public function index(CustomerLicense $license)
    {
        $devices = $this->deviceService->getCustomerDevices($license);
        return DeviceResource::collection($devices);
    }

    public function bulkAction(Request $request, CustomerLicense $license)
    {
        // التحقق من صحة البيانات القادمة في الـ Body
        $request->validate([
            'device_ids' => 'required|array',
            'action' => 'required|in:Suspend,Activate'
        ]);

        // الآن نمرر الـ ID الخاص بالعميل للخدمة بشكل صحيح!
        $this->deviceService->updateDevicesStatus(
            $license->id,
            $request->device_ids,
            $request->action
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث حالة الأجهزة بنجاح لهذا العميل'
        ]);
    }
}
