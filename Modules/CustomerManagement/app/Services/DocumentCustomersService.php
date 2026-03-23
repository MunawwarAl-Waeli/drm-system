<?php
namespace Modules\CustomerManagement\Services;

use Modules\Library\Models\Document;
use Modules\CustomerManagement\Models\CustomerLicense;

class DocumentCustomersService
{
    public function getFilteredCustomers(
        Document $document,
        ?string $search = null,
        string $accessFilter = 'all',
        string $sortBy = 'name',
        string $sortDirection = 'asc',
        int $perPage = 25
    ) {
        $query = CustomerLicense::select('customer_licenses.*');

        // البحث النصي
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('company', 'LIKE', "%{$search}%");
            });
        }

        // فلتر الوصول (تم التغيير إلى علاقة documents وجدول license_documents)
        if ($accessFilter === 'with_access') {
            $query->whereHas('documents', function ($q) use ($document) {
                $q->where('documents.id', $document->id)
                    ->where('license_documents.status', 'active');
            });
        } elseif ($accessFilter === 'without_access') {
            $query->whereDoesntHave('documents', function ($q) use ($document) {
                $q->where('documents.id', $document->id)
                    ->where('license_documents.status', 'active');
            });
        }

        // الترتيب
        $allowedCustomerColumns = ['name', 'email', 'company', 'id'];

        if (in_array($sortBy, $allowedCustomerColumns)) {
            $query->orderBy("customer_licenses.{$sortBy}", $sortDirection);
        } elseif ($sortBy === 'date' || $sortBy === 'valid_from') {
            // دمج الجدول الوسيط الخاص بالمستندات
            $query->leftJoin('license_documents', function ($join) use ($document) {
                $join->on('customer_licenses.id', '=', 'license_documents.customer_license_id')
                    ->where('license_documents.document_id', $document->id);
            });

            $pivotColumn = ($sortBy === 'date') ? 'created_at' : 'valid_from';
            $query->orderBy("license_documents.{$pivotColumn}", $sortDirection);
        }

        // التحميل المسبق وتمرير الـ ID
        return $query->with([
            'documents' => function ($q) use ($document) {
                $q->where('documents.id', $document->id);
            }
        ])->paginate($perPage);
    }

    public function executeBulkAction(
        Document $document,
        array $customerIds,
        string $action,
        ?string $validFrom = null,
        ?string $validUntil = null
    ) {
        $syncData = [];

        if ($action === 'grant_unlimited') {
            foreach ($customerIds as $id) {
                $syncData[$id] = [
                    'access_mode' => 'unlimited',
                    'status' => 'active',
                    'valid_from' => null,
                    'valid_until' => null
                ];
            }
        } elseif ($action === 'grant_limited') {
            foreach ($customerIds as $id) {
                $syncData[$id] = [
                    'access_mode' => 'limited',
                    'status' => 'active',
                    'valid_from' => $validFrom,
                    'valid_until' => $validUntil
                ];
            }
        } elseif ($action === 'revoke_access') {
            foreach ($customerIds as $id) {
                $syncData[$id] = ['status' => 'revoked'];
            }
        }

        // ملاحظة: تأكد من اسم العلاقة في موديل Document (يُفضل أن تكون customerLicenses)
        $document->customerLicenses()->syncWithoutDetaching($syncData);

        return true;
    }
}
?>