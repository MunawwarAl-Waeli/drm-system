<?php

namespace Modules\Library\Services;

use Illuminate\Support\Facades\DB;
// use Exception;
use Modules\Library\Models\Document;
use Modules\Library\Models\DocumentKey;
use Modules\Library\Models\DocumentSecurityControl;

class WriterSyncService
{
    /**
     * حفظ الملفات والمفاتيح والقيود الأمنية القادمة من الـ Writer
     */
    public function syncDocuments(int $publisherId, array $validatedData)
    {

        $typeMep = [
            1 => "pdf",
            2 => "video"
        ];

        $expiryModMap = [
            1 => 'never',
            2 => 'fixed_date',
            3 => 'days_from_first_use',

        ];
        $verifyModMap = [
            1 => 'never',
            2 => 'fixed_date',
            3 => 'only_when_internet',
            4 => 'every_x_days',
            5 => 'after_x_days_then_never',
        ];

        // 2. استخدام Transaction لضمان سلامة الجداول الثلاثة
        return DB::transaction(
        function () use ($publisherId,$validatedData, $typeMep, $expiryModMap, $verifyModMap) {

            $insertedDocuments = [];
            $publicationId = $validatedData['publication_id'] ?? null;

            // 3. المرور على كل ملف في المصفوفة وحفظه
            foreach ($validatedData['documents'] as $docData) {

                // أ. حفظ البيانات الأساسية للمستند
                $document = Document::create([
                    'document_uuid' => $docData['document_uuid'],
                    'title' => $docData['title'],
                    'type' => $typeMep[$docData['type']],
                    'size' => $docData['size'],
                    'file_hash' => $docData['file_hash'],
                    'status' => 'valid',
                    'publisher_id' => $publisherId,
                    'publication_id' => $publicationId,
                ]);

                // ب. حفظ المفتاح المشفر المرتبط بالمستند
                DocumentKey::create([
                    'document_id' => $document->id,
                    'encrypted_key' => $docData['encrypted_key'],
                    'key_version' => 1,
                    'is_active' => true,
                ]);

                // ج. حفظ القيود الأمنية (DRM) المرتبطة بالمستند
                $securityData = $docData['security_controls'];
                DocumentSecurityControl::create([
                    'document_id' => $document->id,
                    'expiry_mode' => $expiryModMap[$securityData['expiry_mode']],
                    'expiry_date' => $securityData['expiry_date'] ?? null,
                    'expiry_days' => $securityData['expiry_days'] ?? null,
                    'verify_mode' => $verifyModMap[$securityData['verify_mode']],
                    'verify_frequency_days' => $securityData['verify_frequency_days'] ?? null,
                    'grace_period_days' => $securityData['grace_period_days'] ?? null,
                ]);

                $insertedDocuments[] = $document->document_uuid;
            }

            return $insertedDocuments;

        });
    }
}
