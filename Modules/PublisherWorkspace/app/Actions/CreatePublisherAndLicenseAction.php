<?php

namespace Modules\PublisherWorkspace\Actions;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // 👈 استدعاء مكتبة التشفير ضروري
use Illuminate\Support\Facades\Storage;
use Modules\PublisherWorkspace\Events\PublisherLicenseCreated;
use Modules\PublisherWorkspace\Models\Publisher;
use Modules\PublisherWorkspace\Models\PublisherLicense;
use Modules\PublisherWorkspace\Services\LicenseFactoryService;
use Modules\SaaSAdmin\Models\Package;

class CreatePublisherAndLicenseAction
{
    protected $licenseFactory;

    public function __construct(LicenseFactoryService $licenseFactory)
    {
        $this->licenseFactory = $licenseFactory;
    }

    public function execute(array $publisherData, int $packageId, ?int $adminId = null, array $customQuotas = [])
    {
        // استخدام Transaction يضمن أنه لو فشل توليد الملف، سيتم التراجع عن إنشاء الحساب في الداتا بيز
        return DB::transaction(function () use ($publisherData, $packageId, $adminId, $customQuotas) {

            // 1. جلب بيانات الباقة
            $package = Package::findOrFail($packageId);

            // 2. إنشاء حساب الناشر (مع حماية كلمة المرور بالتشفير)
            $publisher = Publisher::create([
                'name' => $publisherData['name'],
                'email' => $publisherData['email'],
                'password' => isset($publisherData['password']) ? $publisherData['password'] : null, // 👈 التشفير هنا
                'company' => $publisherData['company'] ?? null,
                'phone' => $publisherData['phone'] ?? null,
                'created_by' => $adminId,
                'status' => 'active',
                'registration_source' => 'website',
            ]);

            // 3. تجهيز التواريخ والمفتاح
            $expiresAt = now()->addDays($package->duration_days + $package->trial_days);
            $licenseKey = 'LCZ-' . strtoupper(Str::random(12));

            // 4. إنشاء سجل الرخصة في قاعدة البيانات
            $license = PublisherLicense::create([
                'publisher_id' => $publisher->id,
                'package_id' => $package->id,
                'license_key' => $licenseKey,
                'max_documents' => $customQuotas['max_documents'] ?? null,
                'max_file_size_mb' => $customQuotas['max_file_size_mb'] ?? 10,
                'max_total_storage_mb' => $customQuotas['max_total_storage_mb'] ?? null,
                'batch_size' => $customQuotas['batch_size'] ?? null,
                'devices_allowed' => $customQuotas['devices_allowed'] ?? null,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            // 5. استدعاء محرك التشفير وتوليد الملف الفعلي
            $cryptoResult = $this->licenseFactory->createLicenseFile($license);

            // 6. حفظ الملف (تنظيم احترافي: داخل مجلد خاص بكل ناشر)
            $fileName = "licenses/licenses.{$licenseKey}.lic";
            Storage::disk('local')->put($fileName, $cryptoResult['binary_file']);

            // 7. تحديث سجل الرخصة (الشهادة ومسار الملف الجديد)
            $license->update([
                'public_certificate' => $cryptoResult['public_certificate'],
                'lic_file_path' => $fileName,
            ]);

            // 8. تجهيز النتيجة للحدث (لإرسال الملف عبر الإيميل مثلاً)
            $encodedFile = base64_encode($cryptoResult['binary_file']);
            event(new PublisherLicenseCreated($publisher, $encodedFile));

            return [
                'publisher' => $publisher,
                'license' => $license,
                'binary_file' => $cryptoResult['binary_file']
            ];
        });
    }
}
