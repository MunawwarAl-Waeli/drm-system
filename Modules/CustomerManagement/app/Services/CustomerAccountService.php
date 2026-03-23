<?php
// Coustomer Account
namespace Modules\CustomerManagement\Services;

use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\Library\Models\Document;
use Modules\Library\Models\Publication;


class CustomerAccountService
{
    /**
     * جلب العملاء (الأساس جاهز لإضافة الفلاتر لاحقاً)
     */
    public function getCustomers()
    {
        // جلب أحدث العملاء. (لاحقاً سنضيف هنا دالة الفلترة when() بكل سهولة)
        return CustomerLicense::latest()->paginate(25);
    }

    /**
     * تنفيذ الإجراءات الجماعية (الـ 9 إجراءات)
     */
    public function executeBulkAction(array $customerIds, string $action, ?int $publicationId = null, ?int $documentId = null)
    {
        // نستخدم استعلام واحد لتحديث مجموعة من العملاء لضمان الأداء العالي
        $query = CustomerLicense::whereIn('id', $customerIds);

        switch ($action) {
            case 'suspend':
                $query->update(['status' => 'suspend']);
                break;
            case 'activate':
                $query->update(['status' => 'active']);
                break;
            case 'delete':
                $query->delete();
                break;
            case 'grant_web_viewer':
                $query->update(['web_viewer_access' => true]);
                break;
            case 'revoke_web_viewer':
                $query->update(['web_viewer_access' => false]);
                break;

            case 'grant_publication':
                if ($publicationId) {
                    $publication = Publication::find($publicationId);
                    $syncData = array_fill_keys($customerIds, ['access_mode' => 'unlimited', 'status' => 'active']);
                    $publication->customerlicense()->syncWithoutDetaching($syncData);
                }
                break;

            case 'grant_document':
                if ($documentId) {
                    $document = Document::find($documentId);
                    $syncData = array_fill_keys($customerIds, ['access_mode' => 'unlimited', 'status' => 'active']);
                    $document->customerlicense()->syncWithoutDetaching($syncData);
                }
                break;

            case 'resend_license':
                // TODO: استدعاء خدمة البريد الإلكتروني لإرسال ملف الرخصة (.llv)
                break;
            case 'resend_web_login':
                // TODO: استدعاء خدمة البريد الإلكتروني لإرسال بيانات الدخول
                break;
        }

        return true;
    }
}
