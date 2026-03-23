<?php

namespace Modules\PublisherWorkspace\Actions;


use Modules\PublisherWorkspace\Models\PublisherLicense;
use Modules\PublisherWorkspace\Models\PublisherDevice;
use Modules\PublisherWorkspace\Services\LicenseFactoryService;
use Exception;

class VerifyWriterAuthAction
{
    protected $cryptoService;

    public function __construct(LicenseFactoryService $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    public function execute(int $licenseId, string $encryptedPayload)
    {
        // 1. البحث عن الرخصة
        $license = PublisherLicense::with('devices')->find($licenseId);
        if (!$license || $license->status !== 'active') {
            throw new Exception("الرخصة غير موجودة أو موقوفة.");
        }

        // 2. فك التشفير والتحقق من التوقيع
        $verifiedData = $this->cryptoService->decryptAndVerifyWriterRequest(
            $encryptedPayload,
            $license->public_certificate
        );

        // $verifiedData الآن تحتوي مثلاً على: ['hardware_id' => 'MAC-123', 'timestamp' => 1670000]

        // 3. التحقق من الجهاز (Hardware ID)
        $hardwareId = $verifiedData['hardware_id'] ?? null;
        if (!$hardwareId) {
            throw new Exception("رقم الجهاز مفقود من الطلب.");
        }

        $device = $license->devices()->where('hardware_id', $hardwareId)->first();

        // إذا كان الجهاز جديداً
        if (!$device) {
            // هل الباقة تسمح بجهاز جديد؟
            if ($license->devices()->count() >= $license->actual_devices_allowed) {
                throw new Exception("لقد تجاوزت الحد الأقصى للأجهزة المسموحة في باقتك.");
            }

            // تسجيل الجهاز الجديد
            $device = PublisherDevice::create([
                'publisher_license_id' => $license->id,
                'hardware_id' => $hardwareId,
                'status' => 'active',
                'last_ip' => request()->ip(),
            ]);
        } elseif ($device->status === 'revoked') {
            throw new Exception("هذا الجهاز تم حظره من قبل الإدارة.");
        }

        // 4. تحديث آخر ظهور للجهاز
        $device->update(['last_ip' => request()->ip()]);

        // 5. تجهيز بيانات الرد (يمكن تشفيرها أيضاً بنفس الطريقة قبل إرسالها للـ C#)
        return [
            'status' => 'authorized',
            'quotas' => [
                'max_documents' => $license->actual_max_documents,
                'batch_size' => $license->actual_batch_size,
            ]
        ];
    }
}
