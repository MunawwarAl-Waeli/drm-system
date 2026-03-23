<?php

namespace Modules\ReaderApp\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Exceptions\HttpResponseException; // 👈 ضروري لرمي الرد الموحد
use Modules\ReaderApp\Models\Reader;
use Modules\CustomerManagement\Models\CustomerDevice;

class ReaderAuthService
{
    /**
     * تسجيل مستخدم جديد
     */
    public function registerReader(array $data, string $ipAddress = null)
    {
        // 1. إنشاء الحساب (تأكد أن المودل يشفر كلمة المرور تلقائياً أو استخدم Hash::make هنا)
        $reader = Reader::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);

        // 2. إصدار التوكن وتسجيل الجهاز
        return $this->generateToken($reader, $data, $ipAddress);
    }

    /**
     * تسجيل دخول مستخدم موجود
     */
    public function loginReader(array $data, string $ipAddress = null)
    {
        $reader = Reader::where('email', $data['email'])->first();

        // فحص صحة البيانات
        if (!$reader || !Hash::check($data['password'], $reader->password)) {
            $this->failAuth('البريد الإلكتروني أو كلمة المرور غير صحيحة.', 'login_failed');
        }

        // فحص حالة الحساب
        if ($reader->is_banned) {
            $this->failAuth('هذا الحساب محظور من استخدام النظام.', 'account_banned', 403);
        }

        // إصدار التوكن وتحديث الجهاز
        return $this->generateToken($reader, $data, $ipAddress);
    }

    /**
     * توليد التوكن وإدارة الأجهزة (Private)
     */
    private function generateToken(Reader $reader, array $data, string $ipAddress = null)
    {
        $deviceInfo = $data['device_info'];
        $hardwareId = $deviceInfo['hardware_id'];

        // 1. البحث عن الجهاز أو إنشاؤه
        $device = CustomerDevice::firstOrCreate(
            [
                'hardware_id' => $hardwareId,
                'reader_id' => $reader->id
            ],
            [
                'status' => 'active',
                'device_name' => $deviceInfo['device_name'],
                'device_type' => $deviceInfo['device_type'],
                'os_version' => $deviceInfo['os_version'],
                'app_version' => $deviceInfo['app_version'] ?? null,
                'ip_address' => $ipAddress,
                'last_synced_at' => now(),
            ]
        );

        // 2. إذا كان الجهاز موجوداً مسبقاً ومحظوراً
        if ($device->status !== 'active') {
            $this->failAuth('تم حظر هذا الجهاز من قبل الإدارة.', 'device_banned', 403);
        }

        // 3. تحديث بيانات الجهاز
        $device->update([
            'last_synced_at' => now(),
            'ip_address' => $ipAddress,
            'device_name' => $deviceInfo['device_name'] ?? $device->device_name,
            'os_version' => $deviceInfo['os_version'] ?? $device->os_version,
        ]);

        // 4. إدارة التوكنات (مسح التوكن القديم لنفس الجهاز وإصدار جديد)
        $reader->tokens()->where('name', 'reader-' . $hardwareId)->delete();
        $token = $reader->createToken('reader-' . $hardwareId)->plainTextToken;

        return [
            'reader' => $reader,
            'token' => $token
        ];
    }

    /**
     * 🏆 الدالة السحرية لتوحيد أخطاء الـ Auth مع الغلاف الموحد
     */
    private function failAuth(string $message, string $action = 'auth_error', int $code = 401)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'action' => $action,
            'message' => $message,
            'data' => null
        ], $code));
    }
}
