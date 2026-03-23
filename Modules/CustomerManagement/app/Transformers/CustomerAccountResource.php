<?php

namespace Modules\CustomerManagement\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CustomerAccountResource extends JsonResource
{
    public function toArray($request)
    {
        // 1. البيانات الأساسية (التي تظهر دائماً في الجدول الرئيسي)
        $registration = $this->registered_at
            ? 'registered at ' . Carbon::parse($this->registered_at)->format('m-d-Y H:i:s')
            : 'not registered';

        $isExpired = $this->valid_until && Carbon::parse($this->valid_until)->isPast();
        $validityText = 'No dates set';

        if ($isExpired) {
            $validityText = 'expired on ' . Carbon::parse($this->valid_until)->format('m-d-Y');
        } elseif ($this->valid_from) {
            $validityText = 'valid from ' . Carbon::parse($this->valid_from)->format('m-d-Y');
        }

        $baseData = [
            'id' => $this->id,
            'name' => $this->name,
            'company' => $this->company ?? '',
            'email' => $this->email,
            'status_ui' => [
                'registration' => $registration,
                'account' => $this->status === 'active' ? 'enabled' : 'suspended',
                'web_viewer' => 'Web Viewer access: ' . ($this->web_viewer_access ?? false ? 'yes' : 'no'),
                'validity' => $validityText,
                'is_expired' => $isExpired
            ]
        ];

        // 2. هل الطلب قادم من دالة "show" (عرض التفاصيل)؟
        // إذا نعم، سنقوم بدمج كل حقول قاعدة البيانات المطلوبة لشاشة التفاصيل
        if ($request->route()->getActionMethod() === 'show') {
            $detailData = [
                'note' => $this->note,
                'type' => $this->type,
                'status' => $this->status,
                'max_devices' => $this->max_devices,

                // تواريخ الصلاحية بصيغة تقبلها حقول الإدخال (Inputs) في الواجهة
                'valid_from' => $this->valid_from ? Carbon::parse($this->valid_from)->format('Y-m-d') : null,
                'valid_until' => $this->valid_until ? Carbon::parse($this->valid_until)->format('Y-m-d') : null,
                'never_expires' => (bool) $this->never_expires,

                'send_via_email' => (bool) $this->send_via_email,

                // إعدادات الموقع
                'restricted_ips' => $this->restricted_ips ?? [],
                'auto_detect_ip' => (bool) $this->auto_detect_ip,
                'restricted_countries' => $this->restricted_countries ?? [],
                'auto_detect_country' => (bool) $this->auto_detect_country,
            ];

            return array_merge($baseData, $detailData);
        }

        // إذا كان الطلب للجدول (index)، نرجع البيانات الأساسية فقط ليكون الطلب خفيفاً وسريعاً
        return $baseData;
    }
}
