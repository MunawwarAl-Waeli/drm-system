<?php

namespace Modules\CustomerManagement\Services;

use Modules\Library\Models\Publication;
use Modules\CustomerManagement\Models\CustomerLicense;

class PublicationCustomersService
{
    /**
     * جلب العملاء مع تطبيق (البحث، الفلترة، الترتيب، والتقسيم)
     */
    public function getFilteredCustomers(
        Publication $publication,
        ?string $search = null,          // نص البحث (Filter)
        string $accessFilter = 'all',    // حالة الوصول (Show)
        string $sortBy = 'name',         // عمود الترتيب (Sort by)
        string $sortDirection = 'asc',   // اتجاه الترتيب
        int $perPage = 25                // عدد العناصر في الصفحة (Show at least)
    ) {
        // 1. نبدأ بتحديد أعمدة العميل فقط لتجنب تداخل الـ ID عند دمج الجداول
        $query =CustomerLicense::select('customer_licenses.*');

        // ==========================================
        // 2. تطبيق البحث النصي (Filter 🔍)
        // ==========================================
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%")
                  ->orWhere('company', 'LIKE', "%{$search}%");
            });
        }

        // ==========================================
        // 3. تطبيق فلتر الوصول (Show with/without access)
        // ==========================================
        if ($accessFilter === 'with_access') {
            $query->whereHas('publications', function ($q) use ($publication) {
                $q->where('publications.id', $publication->id)
                  ->where('license_publications.status', 'active');
            });
        } elseif ($accessFilter === 'without_access') {
            $query->whereDoesntHave('publications', function ($q) use ($publication) {
                $q->where('publications.id', $publication->id)
                  ->where('license_publications.status', 'active');
            });
        }
        // ملاحظة: إذا كان $accessFilter يساوي 'all' فلن نضيف أي شرط هنا

        // ==========================================
        // 4. تطبيق الترتيب (Sort by)
        // ==========================================
        $allowedCustomerColumns = ['name', 'email', 'company', 'id'];

        if (in_array($sortBy, $allowedCustomerColumns)) {
            // ترتيب بناءً على بيانات العميل
            $query->orderBy("customer_licenses.{$sortBy}", $sortDirection);

        } elseif ($sortBy === 'date' || $sortBy === 'valid_from') {
            // ترتيب بناءً على بيانات الجدول الوسيط (يتطلب دمج Left Join)
            $query->leftJoin('license_publications', function ($join) use ($publication) {
                $join->on('customer_licenses.id', '=', 'license_publications.customer_license_id')
                     ->where('license_publications.publication_id', $publication->id);
            });

            // تحديد الحقل المطلوب من الجدول الوسيط
            $pivotColumn = ($sortBy === 'date') ? 'created_at' : 'valid_from';
            $query->orderBy("license_publications.{$pivotColumn}", $sortDirection);
        }

        // ==========================================
        // 5. التحميل المسبق للعلاقة وإرجاع البيانات المقسمة
        // ==========================================
        return $query->with(['publications' => function($q) use ($publication) {
            $q->where('publications.id', $publication->id);
        }])->paginate($perPage);
    }

    /**
     * تنفيذ الإجراءات الجماعية (With all checked)
     */
    public function executeBulkAction(
        Publication $publication,
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

        // تنفيذ الإضافة/التعديل دون حذف بقية العملاء
        $publication->customerlicense()->syncWithoutDetaching($syncData);

        return true;
    }
}
