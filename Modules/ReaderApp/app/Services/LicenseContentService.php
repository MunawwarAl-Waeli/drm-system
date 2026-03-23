<?php

namespace Modules\ReaderApp\Services;

use Modules\CustomerManagement\Models\CustomerLicense;

use Carbon\Carbon;
use Modules\ReaderApp\Transformers\LicensePayloadResource;

class LicenseContentService
{
    public function getLicensePayload(CustomerLicense $license)
    {
        // 1. استدعاء العلاقات
        $this->loadRelations($license);

        $allDocuments = [];

        // 2. معالجة وتقييم المنشورات (Publications)
        foreach ($license->publications as $publication) {
            $pubAccess = $this->evaluateAccess(
                $publication->pivot->status,
                $publication->pivot->valid_from,
                'هذا المنشور موقوف حالياً من قبل الإدارة.',
                'محتوى هذا المنشور سيكون متاحاً في: '
            );

            $publication->evaluated_access = $pubAccess; // إرفاق النتيجة بالكائن

            // معالجة الملفات التابعة للمنشور
            foreach ($publication->documents as $document) {
                $docAccess = $this->evaluateNestedDocument($document, $publication, $license, $pubAccess);
                $docAccess = $this->verifyDocumentKey($document, $docAccess);

                $document->evaluated_access = $docAccess;
                $document->pivot_overrides = null; // لا يوجد تجاوز للـ pivot هنا
                $document->parent_publication_id = $publication->id;

                $allDocuments[] = $document;
            }
        }

        // 3. معالجة وتقييم الملفات الفردية (Individual Documents)
        foreach ($license->documents as $document) {
            $docAccess = $this->evaluateAccess(
                $document->pivot->status,
                $document->pivot->valid_from,
                'هذا الملف موقوف حالياً من قبل الإدارة.',
                'هذا الملف سيكون متاحاً في: '
            );

            $docAccess = $this->verifyDocumentKey($document, $docAccess);

            $document->evaluated_access = $docAccess;
            $document->pivot_overrides = $document->pivot;
            $document->parent_publication_id = null;

            $allDocuments[] = $document;
        }

        // 4. إرفاق كل الملفات بالرخصة لتمريرها للـ Resource
        $license->all_processed_documents = collect($allDocuments);

        // 5. إرجاع الـ Resource الذي سيتولى عملية التغليف (Formatting)
        return new LicensePayloadResource($license);
    }

    /* =========================================================
       دوال التقييم (Business Logic) - ظلت كما هي تماماً
       ========================================================= */

    private function loadRelations(CustomerLicense $license): void
    {
        $license->load([
            'publisher',
            'publications.documents.securityControls',
            'publications.documents.key',
            'documents.securityControls',
            'documents.key'
        ]);
    }

    private function evaluateAccess(string $status, ?string $validFrom, string $suspendMsg, string $futureMsgPrefix): array
    {
        if ($status !== 'active') {
            return ['is_accessible' => false, 'status' => 'suspended', 'reason' => $suspendMsg];
        }

        if ($validFrom && Carbon::parse($validFrom)->isFuture()) {
            return [
                'is_accessible' => false,
                'status' => 'suspended',
                'reason' => $futureMsgPrefix . Carbon::parse($validFrom)->format('Y-m-d')
            ];
        }

        return ['is_accessible' => true, 'status' => 'active', 'reason' => 'متاح للقراءة.'];
    }

    private function evaluateNestedDocument($document, $publication, $license, array $pubAccess): array
    {
        $docAccess = $pubAccess;

        if ($docAccess['is_accessible'] && $document->status !== 'valid') {
            return [
                'is_accessible' => false,
                'status' => 'suspended',
                'reason' => 'عفواً، هذا الملف تم إيقافه مؤقتاً من قبل الإدارة.'
            ];
        }

        if ($docAccess['is_accessible'] && $publication->obey && $license->valid_from) {
            if (Carbon::parse($document->created_at)->isBefore(Carbon::parse($license->valid_from))) {
                return [
                    'is_accessible' => false,
                    'status' => 'suspended',
                    'reason' => 'هذا الملف نُشر قبل تاريخ اشتراكك، ولا تشمله باقتك.'
                ];
            }
        }

        return $docAccess;
    }

    private function verifyDocumentKey($document, array $docAccess): array
    {
        $key = $document->key;
        $hasActiveKey = $key && ($key->is_active == 1 || $key->is_active === true);

        if ($docAccess['is_accessible'] && !$hasActiveKey) {
            return [
                'is_accessible' => false,
                'status' => 'revoked',
                'reason' => 'لا يوجد مفتاح تشفير فعال لهذا الملف.'
            ];
        }

        return $docAccess;
    }
}
