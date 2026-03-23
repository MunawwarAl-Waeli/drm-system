<?php

namespace Modules\ReaderApp\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Modules\CustomerManagement\Models\CustomerDevice;
use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\CustomerManagement\Models\Voucher;
use Modules\ReaderApp\Models\Reader;
use Carbon\Carbon;

class ReaderActivationService
{
    protected $reader;

    public function activate(Reader $reader, array $data, string $ipAddress = null)
    {
        $this->reader = $reader;

        $device = $this->getRegisteredDevice($data['hardware_id'], $ipAddress);

        if ($data['activation_type'] === 'license_file') {
            return $this->activateByLicense($device, $data['license_id']);
        } else {
            return $this->activateByVoucher($device, $data['voucher_code']);
        }
    }

    /**
     * دالة النبض (نقلناها من الكنترولر لتطبيق مبدأ الفصل)
     */
    public function pingDevice(Reader $reader, string $hardwareId, ?string $ipAddress): array
    {
        $device = CustomerDevice::where('hardware_id', $hardwareId)
            ->where('reader_id', $reader->id)
            ->first();

        if (!$device) {
            return ['success' => false, 'action' => 'logout', 'message' => 'هذا الجهاز غير مسجل في النظام. يرجى تسجيل الدخول من جديد.'];
        }

        if ($device->status !== 'active') {
            return ['success' => false, 'action' => 'logout', 'message' => 'تم حظر هذا الجهاز من قبل الإدارة.'];
        }

        $device->update([
            'last_synced_at' => now(),
            'ip_address' => $ipAddress ?? $device->ip_address
        ]);

        return ['success' => true, 'action' => 'continue', 'message' => 'الجهاز متصل ومصرح له.'];
    }

    /* =========================================================
       باقي دوال التفعيل ظلت كما هي (لأنها سليمة منطقياً 100%)
       ========================================================= */

    private function getRegisteredDevice(string $hardwareId, ?string $ipAddress): CustomerDevice
    {
        $device = CustomerDevice::where('hardware_id', $hardwareId)
            ->where('reader_id', $this->reader->id)
            ->first();

        if (!$device)
            $this->fail('هذا الجهاز غير مسجل. يرجى تسجيل الدخول من جديد.', 'logout');
        if ($device->status !== 'active')
            $this->fail('تم حظر هذا الجهاز من قبل الإدارة.', 'logout');

        $device->update([
            'last_synced_at' => now(),
            'ip_address' => $ipAddress ?? $device->ip_address,
        ]);

        return $device;
    }

    private function activateByLicense(CustomerDevice $device, int $licenseId)
    {
        $license = CustomerLicense::find($licenseId);

        if (!$license)
            $this->fail('بيانات الرخصة غير صحيحة أو تم حذفها.');

        $this->validateLicenseStatus($license);

        if ($license->reader_id !== null && $license->reader_id !== $this->reader->id) {
            $this->fail('هذه الرخصة مستخدمة ومربوطة بحساب طالب آخر.');
        }

        if ($license->reader_id === null) {
            $license->update(['reader_id' => $this->reader->id]);
        }

        $this->linkDeviceToLicense($device, $license);

        return $license;
    }

    private function activateByVoucher(CustomerDevice $device, string $voucherCode)
    {
        $voucher = Voucher::where('code', $voucherCode)->first();

        if (!$voucher)
            $this->fail('رقم الكرت غير صحيح.');

        $parentLicense = $voucher->customerLicense;

        $this->validateLicenseStatus($parentLicense, 'الباقة الخاصة بهذا الكرت');

        if ($voucher->is_used && $voucher->used_by_customer_id !== $this->reader->id) {
            $this->fail('هذا الكرت تم استخدامه مسبقاً بحساب طالب آخر.');
        }

        if (!$voucher->is_used) {
            $voucher->update([
                'is_used' => true,
                'used_by_customer_id' => $this->reader->id,
                'activated_at' => now()
            ]);
        }

        $this->linkDeviceToVoucher($device, $voucher, $parentLicense->max_devices);

        return $parentLicense;
    }

    private function validateLicenseStatus($license, string $entityName = 'هذه الرخصة')
    {
        if ($license->status !== 'active') {
            $this->fail("عفواً، {$entityName} محظورة أو موقوفة من قبل الإدارة.", 'revoke');
        }

        if ($license->valid_from && Carbon::parse($license->valid_from)->isFuture()) {
            $startDate = Carbon::parse($license->valid_from)->format('Y-m-d');
            $this->fail("بيانات التفعيل صحيحة، ولكنها لم تبدأ بعد. موعد التفعيل: {$startDate}");
        }
    }

    private function linkDeviceToLicense(CustomerDevice $device, CustomerLicense $license)
    {
        $isDeviceLinked = DB::table('customer_device_license')
            ->where('customer_license_id', $license->id)
            ->where('customer_device_id', $device->id)
            ->exists();

        if (!$isDeviceLinked) {
            $currentDevicesCount = DB::table('customer_device_license')->where('customer_license_id', $license->id)->count();
            if ($currentDevicesCount >= $license->max_devices) {
                $this->fail('لقد استنفدت الحد الأقصى للأجهزة المسموحة لهذه الرخصة.');
            }

            DB::table('customer_device_license')->insert([
                'customer_license_id' => $license->id,
                'customer_device_id' => $device->id,
                'status' => 'active',
                'activated_at' => now(),
            ]);
        }
    }

    private function linkDeviceToVoucher(CustomerDevice $device, Voucher $voucher, int $maxDevices)
    {
        $isDeviceLinked = DB::table('customer_device_voucher')
            ->where('voucher_id', $voucher->id)
            ->where('customer_device_id', $device->id)
            ->exists();

        if (!$isDeviceLinked) {
            $currentDevicesCount = DB::table('customer_device_voucher')->where('voucher_id', $voucher->id)->count();
            if ($currentDevicesCount >= $maxDevices) {
                $this->fail('لقد استنفدت الحد الأقصى للأجهزة المسموحة لهذا الكرت.');
            }

            DB::table('customer_device_voucher')->insert([
                'voucher_id' => $voucher->id,
                'customer_device_id' => $device->id,
                'status' => 'active',
                'activated_at' => now(),
            ]);
        }
    }

    /**
     * تحديث دالة الرمي لتتطابق مع الغلاف الموحد
     */
    private function fail(string $message, string $action = 'error')
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'action' => $action,
            'message' => $message,
            'data' => null
        ], 400));
    }
}
