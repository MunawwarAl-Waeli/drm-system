<?php

namespace Modules\CustomerManagement\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CustomerAccessDocumentResource extends JsonResource
{
    public function toArray($request)
    {
        // القيم الافتراضية
        $directAccess = 'no';
        $expires = 'never';

        // جلب الرخصة من العلاقة المحملة مسبقاً لمعرفة الـ pivot
        $license = $this->customerlicenses->first();

        // فحص هل العميل لديه وصول مباشر ونشط لهذا المستند
        if ($license && $license->pivot && $license->pivot->status === 'active') {
            $directAccess = 'yes';

            // تحديد متى تنتهي الصلاحية إذا كان الوصول محدوداً
            if ($license->pivot->access_mode === 'limited' && $license->pivot->valid_until) {
                $expires = Carbon::parse($license->pivot->valid_until)->format('m-d-Y H:i:s');
            }
        }

        // إرجاع البيانات المهيكلة تماماً كما تظهر في واجهة LockLizard
        return [
            'id' => $this->id,
            'title' => $this->title, // اسم المستند
            'ui_details' => [
                'published' => $this->published_at ? Carbon::parse($this->published_at)->format('m-d-Y H:i:s') : 'N/A',
                'status' => $this->status, // حالة المستند نفسه (valid, suspended)
                'expires' => $expires,
                'direct_access' => $directAccess,
            ]
        ];
    }
}
