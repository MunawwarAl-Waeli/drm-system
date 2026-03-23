<?php

namespace Modules\CustomerManagement\Services;

use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\Library\Models\Document;

class CustomerDocumentAccessService
{
    /**
     * جلب المستندات مع حالة وصول العميل المحدد
     */
    public function getCustomerDocuments(CustomerLicense $license, int $perPage = 25)
    {
        // جلب المستندات الخاصة بالناشر
        return Document::where('publisher_id', $license->publisher_id)
            ->with([
                'customerlicenses' => function ($q) use ($license) {
                    // فلترة العلاقة لنجلب فقط السجل الوسيط (Pivot) الخاص بهذا العميل بالذات
                    $q->where('customer_licenses.id', $license->id);
                }
            ])
            ->paginate($perPage);
    }

    /**
     * تنفيذ تحديث الصلاحيات الجماعي
     */
    public function executeBulkAction(
        CustomerLicense $license,
        array $documentIds,
        string $action,
        ?string $validFrom = null,
        ?string $validUntil = null
    ) {
        $syncData = [];

        if ($action === 'grant_unlimited') {
            foreach ($documentIds as $id) {
                $syncData[$id] = [
                    'access_mode' => 'unlimited',
                    'status' => 'active',
                    'valid_from' => null,
                    'valid_until' => null
                ];
            }
        } elseif ($action === 'grant_limited') {
            foreach ($documentIds as $id) {
                $syncData[$id] = [
                    'access_mode' => 'limited',
                    'status' => 'active',
                    'valid_from' => $validFrom,
                    'valid_until' => $validUntil
                ];
            }
        } elseif ($action === 'revoke_access') {
            foreach ($documentIds as $id) {
                $syncData[$id] = ['status' => 'revoked'];
            }
        }

        // استخدام syncWithoutDetaching للحفاظ على المستندات السابقة للعميل وتحديث/إضافة الجديدة فقط
        $license->documents()->syncWithoutDetaching($syncData);

        return true;
    }
}
