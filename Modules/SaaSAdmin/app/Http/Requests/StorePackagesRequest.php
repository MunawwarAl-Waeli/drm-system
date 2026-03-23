<?php

namespace Modules\SaaSAdmin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePackagesRequest extends FormRequest // (يفضل الإفراد في اسم الكلاس حسب معايير لارافل)
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // اللمسة الأولى: منع تكرار اسم الباقة
            'name' => 'required|string|max:255|unique:packages,name',
            'base_price' => 'required|numeric|min:0',
            'license_type' => 'required|in:Standard,Trial,Enterprise,Educational',
            'security_grade' => 'required|in:Basic,Standard,High,Ultra',
            'base_max_documents' => 'required|integer|min:0',
            'base_max_file_size_mb' => 'required|integer|min:0',
            'base_max_total_storage_mb' => 'required|integer|min:0',
            'base_batch_size' => 'required|integer|min:1',
            'base_devices_allowed' => 'required|integer|min:1',
            'allowed_extensions' => 'nullable|array',
            'allowed_extensions.*' => 'string',
            'can_use_guest_link' => 'boolean',
            'can_use_dynamic_watermark' => 'boolean',
            'allow_custom_splash_screen' => 'boolean',
            'remove_vendor_watermark' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // اللمسة الثانية: رسائل خطأ موجهة ومفهومة
    public function messages()
    {
        return [
            'name.unique' => 'اسم الباقة مستخدم مسبقاً، يرجى اختيار اسم آخر.',
            'license_type.in' => 'نوع الرخصة غير صالح، يجب أن يكون من الخيارات المحددة.',
            'allowed_extensions.array' => 'صيغ الملفات يجب أن تُرسل كمصفوفة (Array).',
        ];
    }
}
