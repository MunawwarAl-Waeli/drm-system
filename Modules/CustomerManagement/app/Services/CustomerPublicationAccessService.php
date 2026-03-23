<?php

namespace Modules\CustomerManagement\Services;

use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\Library\Models\Publication;

class CustomerPublicationAccessService
{
    public function getCustomerPublications(CustomerLicense $license, int $perPage = 25)
    {
        // نجلب جميع المنشورات التابعة للناشر، مع حالة وصول هذا العميل تحديداً
        return Publication::where('publisher_id', $license->publisher_id)
            ->with([
                'customerlicenses' => function ($q) use ($license) {
                    // فلترة العلاقة لتجلب فقط السجل الوسيط (Pivot) الخاص بهذا العميل
                    $q->where('customer_licenses.id', $license->id);
                }
            ])
            ->paginate($perPage);
    }

    public function executeBulkAction(
        CustomerLicense $license,
        array $publicationIds,
        string $action,
        ?string $validFrom = null,
        ?string $validUntil = null
    ) {
        $syncData = [];

        if ($action === 'grant_unlimited') {
            foreach ($publicationIds as $id) {
                $syncData[$id] = [
                    'access_mode' => 'unlimited',
                    'status' => 'active',
                    'valid_from' => null,
                    'valid_until' => null
                ];
            }
        } elseif ($action === 'grant_limited') {
            foreach ($publicationIds as $id) {
                $syncData[$id] = [
                    'access_mode' => 'limited',
                    'status' => 'active',
                    'valid_from' => $validFrom,
                    'valid_until' => $validUntil
                ];
            }
        } elseif ($action === 'revoke_access') {
            foreach ($publicationIds as $id) {
                $syncData[$id] = ['status' => 'revoked'];
            }
        }

        // حفظ التعديلات في الجدول الوسيط دون المساس بالصلاحيات الأخرى للعميل
        $license->publications()->syncWithoutDetaching($syncData);

        return true;
    }
}
