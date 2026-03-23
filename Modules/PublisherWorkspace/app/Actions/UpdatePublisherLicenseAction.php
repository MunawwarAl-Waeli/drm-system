<?php

namespace Modules\PublisherWorkspace\Actions;


use Illuminate\Support\Facades\DB;
use Modules\PublisherWorkspace\Models\PublisherLicense;
use Modules\PublisherWorkspace\Services\LicenseFactoryService;

class UpdatePublisherLicenseAction
{
    protected $licenseFactory;

    public function __construct(LicenseFactoryService $licenseFactory)
    {
        $this->licenseFactory = $licenseFactory;
    }

    /**
     * تحديث رخصة الناشر (ترقية باقة، إضافة استثناءات، أو إيقاف الحساب)
     * * @param bool $regenerateFile إذا كانت true، سيقوم النظام بتوليد ملف LZPK جديد بالقيود المحدثة
     */
    public function execute(PublisherLicense $license, array $updateData, bool $regenerateFile = false)
    {
        return DB::transaction(function () use ($license, $updateData, $regenerateFile) {

            // 1. تحديث بيانات الرخصة في قاعدة البيانات (الباقة أو الاستثناءات الجديدة)
            $license->update([
                'package_id' => $updateData['package_id'] ?? $license->package_id,
                'custom_max_documents' => $updateData['custom_max_documents'] ?? $license->custom_max_documents,
                'custom_max_file_size_mb' => $updateData['custom_max_file_size_mb'] ?? $license->custom_max_file_size_mb,
                'custom_max_total_storage_mb' => $updateData['custom_max_total_storage_mb'] ?? $license->custom_max_total_storage_mb,
                'custom_batch_size' => $updateData['custom_batch_size'] ?? $license->custom_batch_size,
                'custom_devices_allowed' => $updateData['custom_devices_allowed'] ?? $license->custom_devices_allowed,
                'status' => $updateData['status'] ?? $license->status,
                'expires_at' => $updateData['expires_at'] ?? $license->expires_at,
            ]);

            // مصفوفة النتيجة الأساسية
            $result = [
                'license' => $license,
                'file_regenerated' => false
            ];

            // 2. إذا طلب المدير توليد ملف جديد بالقيود المحدثة (للعمل الأوفلاين)
            if ($regenerateFile) {
                // نستدعي محرك التشفير ليقرأ القيود الجديدة من الداتابيز ويصنع ملفاً جديداً
                $cryptoResult = $this->licenseFactory->createLicenseFile($license);

                // نحدث الشهادة العامة في الداتابيز (لأن المفاتيح تغيرت)
                $license->update([
                    'public_certificate' => $cryptoResult['public_certificate']
                ]);

                // نرفق الملف الجديد في النتيجة ليتم تحميله وإرساله للناشر
                $result['binary_file'] = $cryptoResult['binary_file'];
                $result['file_regenerated'] = true;
            }

            return $result;
        });
    }
}
