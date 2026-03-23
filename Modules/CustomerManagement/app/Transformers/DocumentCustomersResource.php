<?php

namespace Modules\CustomerManagement\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentCustomersResource extends JsonResource
{
    public function toArray($request)
    {
        // تجهيز نصوص الحالة (Status)
        $registration = $this->registered_at
            ? 'registered at ' . $this->registered_at->format('m-d-Y H:i:s')
            : 'not registered';

        $accountStatus = $this->status === 'active' ? 'enabled' : 'disabled';

        $validFrom = $this->valid_from
            ? 'valid from ' . $this->valid_from->format('m-d-Y')
            : 'no date set';

        // تجهيز نص الوصول (Access)
        $accessText = 'No Access';

        // 1. جلب المستند الأول من الذاكرة بدلاً من المنشور
        $document = $this->documents->first();

        // 2. الفحص على المستند
        if ($document && $document->pivot) {
            // 3. قراءة الحالة من الـ pivot الخاص بالمستند
            if ($document->pivot->status === 'active') {
                $accessText = $document->pivot->access_mode === 'unlimited'
                    ? 'Yes - unlimited access'
                    : 'Yes - valid until ' . ($document->pivot->valid_until ?
                        \Carbon\Carbon::parse($document->pivot->valid_until)->format('m-d-Y') : 'No Date Set');
            } else {
                $accessText = 'Revoked / Suspended';
            }
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'company' => $this->company,
            'email' => $this->email,
            'ui_status' => [
                'registration' => $registration,
                'account_status' => $accountStatus,
                'valid_from' => $validFrom,
            ],
            'ui_access' => $accessText,
        ];
    }
}
