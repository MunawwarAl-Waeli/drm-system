<?php
namespace Modules\CustomerManagement\Services;

use Illuminate\Support\Facades\DB;
use Modules\CustomerManagement\Models\CustomerLicense;

class DeviceService
{
    public function getCustomerDevices(CustomerLicense $license)
    {
        // العلاقة يجب أن تكون معرّفة في موديل CustomerLicense
        return $license->devices()->get();
    }

    public function updateDevicesStatus(int $licenseId, array $deviceIds, string $action)
    {
        $status = ($action === 'Suspend') ? 'suspend' : 'active';

        // التحديث يتم في الجدول الوسيط لضمان عدم التأثير على رخص أو ناشرين آخرين
        return DB::table('customer_device_license')
            ->where('customer_license_id', $licenseId)
            ->whereIn('customer_device_id', $deviceIds)
            ->update(['status' => $status]);
    }
}
