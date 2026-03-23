<?php

namespace Modules\SaaSAdmin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePublisherAndLicenseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // --- بيانات الناشر الأساسية ---
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:publishers,email', // يجب ألا يكون مسجلاً من قبل
            'company' => 'nullable|string|max:255',

            // --- بيانات الرخصة ---
            'package_id' => 'required|exists:packages,id', // يجب أن تكون الباقة موجودة في الداتابيز فعلاً

            // --- الاستثناءات (Overrides) - اختيارية ---
            'custom_max_documents' => 'nullable|integer|min:0',
            'custom_max_file_size_mb' => 'nullable|integer|min:0',
            'custom_max_total_storage_mb' => 'nullable|integer|min:0',
            'custom_batch_size' => 'nullable|integer|min:1',
            'custom_devices_allowed' => 'nullable|integer|min:1',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'هذا البريد الإلكتروني مسجل لناشر آخر بالفعل.',
            'package_id.exists' => 'الباقة المختارة غير موجودة في النظام.',
        ];
    }
}
