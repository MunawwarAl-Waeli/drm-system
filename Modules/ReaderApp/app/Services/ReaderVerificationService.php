<?php

namespace Modules\ReaderApp\Services;

use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\CustomerManagement\Models\CustomerDevice;
use Modules\Library\Models\Document;
use Modules\ReaderApp\Transformers\Verification\DocumentVerificationResource;
use Carbon\Carbon;

class ReaderVerificationService
{
    /**
     * الدالة الرئيسية: الفحص الشامل وتقييم الوصول
     */
    public function verifyDocument($reader, array $data)
    {
        $docId = $data['document_id'];
        $licenseId = $data['license_id'];
        $hardwareId = $data['hardware_id'];

        // 1. الفحص الأمني للجهاز
        $deviceError = $this->verifyDevice($reader->id, $hardwareId, $licenseId);
        if ($deviceError) {
            return $this->prepareResult('revoke', 'device', $deviceError, $docId);
        }

        // 2. فحص الرخصة الأم
        $license = CustomerLicense::find($licenseId);
        $licenseError = $this->verifyLicense($license);
        if ($licenseError) {
            return $this->prepareResult('revoke', 'license', $licenseError, $docId);
        }

        // 3. جلب الملف مع المفتاح (بشكل مفرد وليس مصفوفة)
        $document = Document::with([
            'securityControls',
            'key' => fn($q) => $q->where('is_active', true)
        ])->find($docId);

        if (!$document) {
            return $this->prepareResult('revoke', 'document', 'الملف غير موجود في النظام.', $docId);
        }

        // 4. التوجيه الذكي للفحص بناءً على النطاق (Access Scope)
        $scopeResult = $this->verifyAccessScope($document, $license);
        if (!$scopeResult['is_valid']) {
            return $this->prepareResult($scopeResult['action'], $scopeResult['type'], $scopeResult['message'], $docId);
        }

        // 5. فحص حالة الملف نفسه والمفتاح الأمني
        if ($document->status !== 'valid') { // استخدمنا valid لتطابق الـ Migration
            return $this->prepareResult('suspend', 'document', 'هذا الملف معلق حالياً من قبل الإدارة.', $docId);
        }

        if (!$document->key) { // التصحيح المهم: key بدلاً من keys->isEmpty()
            return $this->prepareResult('revoke', 'document', 'تم إبطال المفتاح الأمني لهذا الملف.', $docId);
        }

        // 6. النجاح التام: دمج القواعد وإرسالها
        $document->pivot_overrides = $scopeResult['pivot_overrides'] ?? null;
        return $this->prepareResult('update_rules', 'document', 'تم التحقق بنجاح.', $docId, $document);
    }

    /* =========================================================
       دوال الفحص والتقييم (Validators)
       ========================================================= */

    private function verifyDevice(int $readerId, string $hardwareId, int $licenseId): ?string
    {
        $device = CustomerDevice::where('hardware_id', $hardwareId)
            ->where('reader_id', $readerId)
            ->first();

        if (!$device) {
            return 'الجهاز غير مسجل.';
        }
        if ($device->status !== 'active') {
            return 'تم حظر هذا الجهاز.';
        }
        if (!$device->hasAccessToLicense($licenseId)) {
            return 'الجهاز غير مصرح له بفتح ملفات هذه الرخصة.';
        }

        return null; // لا يوجد خطأ
    }

    private function verifyLicense(?CustomerLicense $license): ?string
    {
        if (!$license || $license->status !== 'active') {
            return 'الرخصة محظورة أو غير موجودة.';
        }

        if ($license->valid_until && Carbon::parse($license->valid_until)->isPast()) {
            return 'انتهت صلاحية الرخصة.';
        }

        return null; // لا يوجد خطأ
    }

    private function verifyAccessScope(Document $document, CustomerLicense $license): array
    {
        $result = ['is_valid' => true, 'pivot_overrides' => null];

        switch ($document->access_scope) {

            case 'publication':
                // الملف محبوس في كورس (نجلب رقم الكورس من الملف نفسه)
                $pubPivot = $license->publications()->where('publication_id', $document->publication_id)->first()?->pivot;

                if (!$pubPivot) {
                    return ['is_valid' => false, 'action' => 'revoke', 'type' => 'publication', 'message' => 'هذا الملف يتبع لمنشور لم يعد ضمن رخصتك.'];
                }
                if ($pubPivot->status !== 'active') {
                    return ['is_valid' => false, 'action' => 'suspend', 'type' => 'publication', 'message' => 'المنشور التابع له هذا الملف معلق حالياً من قبل الإدارة.'];
                }
                break;

            case 'selected_customers':
                // الملف يُباع فردياً
                $docPivot = $license->documents()->where('document_uuid', $document->id)->first()?->pivot;

                if (!$docPivot) {
                    return ['is_valid' => false, 'action' => 'revoke', 'type' => 'document', 'message' => 'هذا الملف الفردي غير مدرج ضمن رخصتك.'];
                }
                if (($docPivot->status ?? 'active') !== 'active') {
                    return ['is_valid' => false, 'action' => 'suspend', 'type' => 'document', 'message' => 'تم إيقاف صلاحيتك لهذا الملف من الإدارة.'];
                }
                // نحتفظ بتجاوزات الملف الفردي (مرات الطباعة والمشاهدة) لتطبيقها
                $result['pivot_overrides'] = $docPivot;
                break;

            case 'all_customers':
                // الملف عام لأي رخصة صالحة (لا يحتاج فحص وسيط)
                break;
        }

        return $result;
    }

    /* =========================================================
       دالة التغليف والتشكيل (Formatter)
       ========================================================= */

    private function prepareResult($action, $type, $message, $docId, $document = null)
    {
        // استخدام document_uuid بناءً على جدولك الجديد
        $responseData = [
            'type' => $type,
            'document_uuid' => $document->document_uuid ?? $docId
        ];

        // في حالة النجاح ندمج قواعد الحماية
        if ($document && $action === 'update_rules') {
            $flatRules = (new DocumentVerificationResource($document))->resolve();
            $responseData = array_merge($responseData, $flatRules);
        }

        return [
            'success' => true,
            'action' => $action,
            'message' => $message,
            'data' => $responseData
        ];
    }
}
