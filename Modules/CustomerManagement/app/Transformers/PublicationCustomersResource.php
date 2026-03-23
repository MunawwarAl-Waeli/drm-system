<?php

namespace Modules\CustomerManagement\Transformers;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;


class PublicationCustomersResource extends JsonResource
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

        // تجهيز نص الوصول (Access) من الجدول الوسيط
        $accessText = 'No Access';

        // 1. جلب المنشور الأول من الذاكرة (لاحظ حرف الـ s في publications والسبيلنج الصحيح لـ first)
        $publication = $this->publications->first();



        // 2. الفحص يجب أن يكون على المنشور وليس على العميل
        if ($publication && $publication->pivot) {

            // 3. قراءة الحالة من الـ pivot الخاص بالمنشور
            if ($publication->pivot->status === 'active') {

                // دمجناها في سطر واحد نظيف تماماً كما تحب!
                $accessText = $publication->pivot->access_mode === 'unlimited'
                    ? 'Yes - unlimited access'
                    : 'Yes - valid until ' . ($publication->pivot->valid_until ? \Carbon\Carbon::parse($publication->pivot->valid_until)->format('m-d-Y') : 'No Date Set');

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
