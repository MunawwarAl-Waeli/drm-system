<?php

namespace Modules\Library\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Library\Models\Document;
use Modules\Library\Models\DocumentKey;
use Modules\Library\Models\DocumentSecurityControl;


class SyncWriterDocumentsAction
{
    /**
     * تنفيذ دورة حفظ الملفات المشفرة وقيودها ومفاتيحها
     */
    public function execute(int $publisherId, ?int $publicationId, array $documentsData)
    {
        // نفتح Transaction لضمان سلامة كل الملفات
        return DB::transaction(function () use ($publisherId, $publicationId, $documentsData) {

            $savedDocuments = [];

            // نمر على كل ملف قادم في المصفوفة (سواء كان ملفاً واحداً أو 100)
            foreach ($documentsData as $docData) {

                // 1. حفظ البيانات الأساسية للملف في جدول documents
                $document = Document::create([
                    'document_uuid' => $docData['document_uuid'],
                    'title' => $docData['title'],
                    'type' => $docData['type'] ?? 'pdf',
                    'size' => $docData['size'],
                    'file_hash' => $docData['file_hash'],
                    'status' => 'valid',
                    'published_at' => now(), // نعتبره نُشر لحظة المزامنة
                    'publisher_id' => $publisherId,
                    'publication_id' => $publicationId, // قد يكون null إذا كان ملفاً حراً
                ]);

                // 2. حفظ المفتاح المشفر في "القبو" (جدول document_keys)
                DocumentKey::create([
                    'document_id' => $document->id,
                    'encrypted_key' => $docData['encrypted_key'],
                    'key_version' => 1,
                    'is_active' => true,
                ]);

                // 3. حفظ القيود الأمنية في "دماغ الـ DRM" (جدول document_security_controls)
                $controls = $docData['security_controls'];
                DocumentSecurityControl::create([
                    'document_id' => $document->id,
                    'expiry_mode' => $controls['expiry_mode'],
                    'expiry_date' => $controls['expiry_date'] ?? null,
                    'expiry_days' => $controls['expiry_days'] ?? null,
                    'verify_mode' => $controls['verify_mode'],
                    'verify_frequency_days' => $controls['verify_frequency_days'] ?? null,
                    'grace_period_days' => $controls['grace_period_days'] ?? null,
                    'max_views_allowed' => $controls['max_views_allowed'] ?? null,
                    'allow_printing' => $controls['allow_printing'],
                    'print_limit' => $controls['print_limit'] ?? null,
                    'prevent_screen_capture' => $controls['prevent_screen_capture'],
                    'watermark_settings' => $controls['watermark_settings'] ?? null,
                ]);

                // نجمع الملفات المحفوظة لنرد بها على الـ C#
                $savedDocuments[] = $document;
            }

            // إرجاع مصفوفة الملفات المحفوظة (Eloquent Models)
            return $savedDocuments;

        }); // نهاية الـ Transaction
    }
}
