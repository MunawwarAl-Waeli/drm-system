<?php

namespace Modules\ReaderApp\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use Modules\ReaderApp\Transformers\Traits\ExtractsFlatRulesTrait;

class DocumentResource extends JsonResource
{
    use ExtractsFlatRulesTrait;
    public function toArray($request)
    {
        $access = $this->evaluated_access;
        $key = $this->key;

        // حالة إبطال المفتاح من الإدارة
        $hasActiveKey = $key && ($key->is_active == 1 || $key->is_active === true);
        if ($access['is_accessible'] && !$hasActiveKey) {
            $access = [
                'is_accessible' => false,
                'status' => 'revoked',
                'reason' => 'لا يوجد مفتاح تشفير فعال لهذا الملف.'
            ];
        }

        // بناء الـ Data المسطحة
        $flatData = null;
        if ($access['is_accessible']) {
            $flatData = array_merge([
                'title' => $this->name ?? $this->title,
                'key_version' => $key->key_version,
                'encrypted_key' => $key->encrypted_key,
                'file_hash' => $this->file_hash,
            ], $this->extractFlatRules($this,$this->pivot_overrides));
        }

        return [
            'document_uuid' => $this->document_uuid,
            'publication_id' => $this->parent_publication_id,
            'status' => $access['status'],
            'message' => $access['reason'],
            'data' => $flatData // 👈 مسطح بالكامل أو Null
        ];
    }

}
