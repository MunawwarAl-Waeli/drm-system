<?php

namespace Modules\ReaderApp\Services;

use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\CustomerManagement\Models\CustomerDevice;
use Modules\ReaderApp\Transformers\Sync\SyncPayloadResource;
use Carbon\Carbon;

class ReaderSyncService
{
    public function syncContent($reader, array $data)
    {
        $lastSync = Carbon::parse($data['last_sync_date']);

        $license = $this->fetchLicense($data['license_id']);
        if (!$license) {
            return ['success' => false, 'message' => 'الرخصة غير موجودة.'];
        }

        // الفحص الأمني المزدوج للجهاز
        $deviceCheck = $this->verifyAndUpdateDevice($reader->id, $data['hardware_id'], $license->id);
        if (!$deviceCheck['success']) {
            return $deviceCheck;
        }

        // معالجة التحديثات وتقييمها
        $licenseUpdates = $this->getLicenseUpdates($license, $lastSync);
        $pubResult = $this->processPublications($license, $lastSync);
        $indivDocs = $this->processIndividualDocuments($license, $lastSync);

        $allDocuments = array_merge($pubResult['documents'], $indivDocs);

        // إلصاق البيانات بالكائن لتمريرها للـ Resource
        $license->sync_license_updates = $licenseUpdates;
        $license->sync_publications = collect($pubResult['publications']);
        $license->sync_documents = collect($allDocuments);
        $license->has_any_updates = $licenseUpdates !== null || $license->sync_publications->isNotEmpty() || $license->sync_documents->isNotEmpty();

        return new SyncPayloadResource($license);
    }

    private function verifyAndUpdateDevice(int $readerId, string $hardwareId, int $licenseId): array
    {
        $device = CustomerDevice::where('hardware_id', $hardwareId)
            ->where('reader_id', $readerId)
            ->first();

        if (!$device) {
            return ['success' => false, 'message' => 'هذا الجهاز غير مسجل. يرجى تسجيل الدخول.'];
        }

        if ($device->status !== 'active') {
            return ['success' => false, 'message' => 'تم حظر هذا الجهاز من قبل الإدارة.'];
        }

        // الاستدعاء النظيف من الموديل
        if (!$device->hasAccessToLicense($licenseId)) {
            return ['success' => false, 'message' => 'هذا الجهاز غير مصرح له بمزامنة هذه الرخصة أو تم إيقافه منها.'];
        }

        $device->update(['last_synced_at' => now()]);

        return ['success' => true];
    }

    private function fetchLicense(int $licenseId)
    {
        return CustomerLicense::with([
            'publications.documents.securityControls',
            'publications.documents.key',
            'documents.securityControls',
            'documents.key'
        ])->find($licenseId);
    }

    private function getLicenseUpdates($license, Carbon $lastSync): ?array
    {
        if ($license->updated_at <= $lastSync)
            return null;

        return [
            'sync_action' => 'update',
            'status' => $license->status,
            'message' => 'تم تحديث بيانات الرخصة.',
            'data' => [
                'valid_until' => $license->never_expires ? null : ($license->valid_until ? Carbon::parse($license->valid_until)->format('Y-m-d H:i:s') : null),
            ]
        ];
    }

    private function processPublications($license, Carbon $lastSync): array
    {
        $publications = [];
        $documents = [];

        foreach ($license->publications as $publication) {
            $pivot = $publication->pivot;
            $pubAccess = $this->evaluateAccess($pivot->status, $pivot->valid_from, 'هذا المنشور موقوف حالياً من قبل الإدارة.');
            $isPubNew = $pivot->created_at > $lastSync;
            $isPubUpdated = $pivot->updated_at > $lastSync && !$isPubNew;

            if ($isPubNew || $isPubUpdated) {
                $publication->is_new_sync = $isPubNew;
                $publication->evaluated_access = $pubAccess;
                $publications[] = $publication;
            }

            $docsUpdates = $this->processNestedDocuments($publication, $license, $pivot, $lastSync, $pubAccess, $isPubNew);
            $documents = array_merge($documents, $docsUpdates);
        }

        return ['publications' => $publications, 'documents' => $documents];
    }

    private function processNestedDocuments($publication, $license, $pubPivot, Carbon $lastSync, array $pubAccess, bool $isPubNew): array
    {
        $documents = [];

        foreach ($publication->documents as $document) {
            $docAccess = $pubAccess;

            if ($docAccess['is_accessible'] && $document->status !== 'active') {
                $docAccess = ['is_accessible' => false, 'reason' => 'عفواً، هذا الملف تم إيقافه مؤقتاً من قبل الإدارة.'];
            }

            if ($docAccess['is_accessible'] && $publication->obey && $license->valid_from) {
                if (Carbon::parse($document->created_at)->isBefore(Carbon::parse($license->valid_from))) {
                    $docAccess = ['is_accessible' => false, 'reason' => 'هذا الملف نُشر قبل تاريخ اشتراكك، ولا تشمله باقتك.'];
                }
            }

            $isDocNew = $isPubNew || $document->created_at > $lastSync;
            $isDocUpdated = !$isDocNew && $this->isDocumentUpdated($document, $pubPivot, $lastSync);

            if ($isDocNew || $isDocUpdated) {
                $document->is_new_sync = $isDocNew;
                $document->evaluated_access = $docAccess;
                $document->pivot_overrides = null;
                $document->parent_publication_id = $publication->id;
                $documents[] = $document;
            }
        }

        return $documents;
    }

    private function processIndividualDocuments($license, Carbon $lastSync): array
    {
        $documents = [];

        foreach ($license->documents as $document) {
            $pivot = $document->pivot;
            $docAccess = $this->evaluateAccess($pivot->status, $pivot->valid_from, 'هذا الملف موقوف حالياً من قبل الإدارة.');

            if ($docAccess['is_accessible'] && $document->status !== 'active') {
                $docAccess = ['is_accessible' => false, 'reason' => 'عفواً، هذا الملف تم إيقافه مؤقتاً من قبل الإدارة.'];
            }

            $isDocNew = $pivot->created_at > $lastSync;
            $isDocUpdated = !$isDocNew && $this->isDocumentUpdated($document, $pivot, $lastSync);

            if ($isDocNew || $isDocUpdated) {
                $document->is_new_sync = $isDocNew;
                $document->evaluated_access = $docAccess;
                $document->pivot_overrides = $pivot;
                $document->parent_publication_id = null;
                $documents[] = $document;
            }
        }

        return $documents;
    }

    private function evaluateAccess(string $status, ?string $validFrom, string $suspendMessage): array
    {
        if ($status !== 'active')
            return ['is_accessible' => false, 'reason' => $suspendMessage];
        if ($validFrom && Carbon::parse($validFrom)->isFuture()) {
            return ['is_accessible' => false, 'reason' => 'سيكون متاحاً في: ' . Carbon::parse($validFrom)->format('Y-m-d')];
        }
        return ['is_accessible' => true, 'reason' => null];
    }

    private function isDocumentUpdated($document, $pivot, Carbon $lastSync): bool
    {
        return $pivot->updated_at > $lastSync || $document->updated_at > $lastSync;
    }
}
