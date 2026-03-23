<?php

namespace Modules\CustomerManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // 1. المعلومات الأساسية
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|max:255',
            'company' => 'nullable|string|max:255',
            'note' => 'nullable|string',

            // 2. إعدادات الحساب والرخصة
            'type' => 'sometimes|in:individual,group',
            'status' => 'sometimes|in:active,suspend,expired',
            'max_devices' => 'sometimes|integer|min:1',

            // 3. تواريخ الصلاحية
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'never_expires' => 'boolean',

            // 4. خيارات أخرى
            'send_via_email' => 'boolean',

            // 5. تقييد الموقع (Location Restriction)
            'restricted_ips' => 'nullable|array',
            'restricted_ips.*' => 'ip',
            'auto_detect_ip' => 'boolean',

            'restricted_countries' => 'nullable|array',
            'restricted_countries.*' => 'string|size:2', // مثال: YE, SA
            'auto_detect_country' => 'boolean',
        ];
    }

    /**
     * تجهيز البيانات قبل التحقق (لتطبيق قواعد منطقية)
     */
    protected function prepareForValidation()
    {
        // إذا اختار المدير "صلاحية لا تنتهي"، نقوم برمجياً بتفريغ تاريخ النهاية
        if ($this->never_expires) {
            $this->merge([
                'valid_until' => null,
            ]);
        }
    }
}
