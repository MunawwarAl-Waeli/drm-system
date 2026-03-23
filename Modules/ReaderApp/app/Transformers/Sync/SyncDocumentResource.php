<?php

namespace Modules\ReaderApp\Transformers\Sync;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Modules\ReaderApp\Transformers\Traits\ExtractsFlatRulesTrait;

class SyncDocumentResource extends JsonResource
{
    use ExtractsFlatRulesTrait;
    public function toArray($request)
    {
        $access = $this->evaluated_access;
        $isNew = $this->is_new_sync;
        $key = $this->key;
        $hasActiveKey = $key && ($key->is_active == 1 || $key->is_active === true);

        // إذا كان موقوفاً أو بدون مفتاح
        if (!$access['is_accessible'] || !$hasActiveKey) {
            $status = !$hasActiveKey ? 'revoked' : 'suspended';
            return [
                'sync_action' => ($status === 'revoked') ? 'revoke' : 'suspend', // توحيد اسم المفتاح
                'document_uuid' => $this->document_uuid ?? $this->id,
                'publication_id' => $this->parent_publication_id,
                'status' => $status,
                'message' => !$hasActiveKey ? 'تم إبطال المفتاح الأمني لهذا الملف.' : $access['reason'],
                'data' => null // لا نرسل قواعد للملفات المحظورة
            ];
        }

        // استخراج القواعد كقائمة مسطحة (Flat List)
        $flatData = $this->extractFlatRules($this, $this->pivot_overrides);

        if ($isNew) {
            $flatData['title'] = $this->name ?? $this->title;
            $flatData['encrypted_key'] = $key->encrypted_key;
            $flatData['key_version'] = $key->key_version;
        }

        return [
            'sync_action' => $isNew ? 'add' : 'update_rules',
            'document_uuid' => $this->document_uuid ?? $this->id,
            'publication_id' => $this->parent_publication_id,
            'status' => 'active',
            'message' => $isNew ? 'تمت إضافة ملف جديد.' : 'تم تحديث صلاحيات الملف.',
            'data' => $flatData // بيانات مسطحة جاهزة للـ SQLite!
        ];
    }


}
