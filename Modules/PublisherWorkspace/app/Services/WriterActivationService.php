<?php

namespace Modules\PublisherWorkspace\Services;

use Modules\PublisherWorkspace\Models\PublisherLicense;
use Modules\PublisherWorkspace\Models\PublisherDevice;
use Illuminate\Http\Exceptions\HttpResponseException;

class WriterActivationService
{
    /**
     * تفعيل جهاز جديد أو تسجيل دخوله
     */
    public function activateDevice(array $data, string $ipAddress)
    {
        // 1. التحقق المزدوج (الرقم + المفتاح)
        $license = PublisherLicense::with('publisher', 'package')
            ->where('publisher_id', $data['publisher_id'])
            ->where('license_key', $data['license_key'])
            ->first();

        if (!$license || $license->status !== 'active') {
            $this->fail('بيانات الرخصة غير متطابقة أو منتهية.', 'license', 401);
        }

        $publisher = $license->publisher;

        if ($publisher->status !== 'active') {
            $this->fail('حساب الناشر موقوف، يرجى مراجعة الإدارة.', 'account', 403);
        }

        // 2. البحث عن الجهاز أو إنشاؤه
        $device = PublisherDevice::where('publisher_license_id', $license->id)
            ->where('hardware_id', $data['hardware_id'])
            ->first();

        if ($device) {
            if ($device->status === 'revoked') {
                $this->fail('هذا الجهاز محظور من استخدام النظام.', 'device', 403);
            }

            // تحديث بيانات الجهاز
            $device->update([
                'device_name' => $data['device_name'] ?? $device->device_name,
                'last_ip' => $ipAddress,
                'last_synced_at' => now(),
            ]);
        } else {
            // فحص حد الأجهزة المسموح به
            $allowedDevices = $license->package->base_devices_allowed ?? 1;

            $activeCount = PublisherDevice::where('publisher_license_id', $license->id)
                ->where('status', 'active')
                ->count();

            if ($activeCount >= $allowedDevices) {
                $this->fail("لقد وصلت للحد الأقصى للأجهزة ({$allowedDevices}). يرجى تسجيل الخروج من جهاز آخر أولاً.", 'device', 403);
            }

            // إنشاء الجهاز الجديد
            $device = PublisherDevice::create([
                'publisher_id' => $publisher->id,
                'publisher_license_id' => $license->id,
                'hardware_id' => $data['hardware_id'],
                'device_name' => $data['device_name'] ?? 'Unknown Device',
                'os_version' => $data['os_version']??null,
                'app_version' => $data['app_version']??null,
                'last_ip' => $ipAddress,
                'last_synced_at' => now(),
                'status' => 'active'
            ]);
        }

        // 3. إصدار التوكن
        $publisher->tokens()->where('name', 'WriterApp_' . $data['hardware_id'])->delete();
        $token = $publisher->createToken('WriterApp_' . $data['hardware_id'], ['writer-access'])->plainTextToken;

        return [
            'token' => $token,
            'publisher' => $publisher,
            'device_id' => $device->id
        ];
    }

    /**
     * نبض الجهاز (Ping)
     */
    public function pingDevice($publisher, string $hardwareId, string $ipAddress)
    {
        $device = PublisherDevice::where('publisher_id', $publisher->id)
            ->where('hardware_id', $hardwareId)
            ->first();

        if (!$device) {
            $this->fail('الجهاز غير مسجل في النظام.', 'device', 404);
        }

        // فحص الحظر للناشر أو الجهاز
        if ($publisher->status !== 'active' || $device->status === 'revoked') {
            // حذف التوكن الحالي لطرده
            $publisher->currentAccessToken()->delete();
            $this->fail('تم حظر الحساب أو الجهاز، يرجى تسجيل الخروج.', 'logout', 403);
        }

        // تحديث النبض
        $device->update([
            'last_ip' => $ipAddress,
            'last_synced_at' => now(),
        ]);

        return true;
    }

    /**
     * دالة لرمي خطأ متوافق مع الغلاف الموحد
     */
    private function fail(string $message, string $action, int $code = 400)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'action' => $action,
            'message' => $message,
            'data' => null
        ], $code));
    }
}
