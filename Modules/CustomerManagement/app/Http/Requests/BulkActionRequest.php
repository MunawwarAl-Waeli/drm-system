<?php

namespace Modules\CustomerManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkActionRequest extends FormRequest
{
    /**
     * تحديد ما إذا كان المستخدم مصرحاً له بالقيام بهذا الطلب.
     * (يمكنك لاحقاً ربطها بنظام الصلاحيات Spatie Roles/Permissions).
     */
    public function authorize()
    {
        return true;
    }

    /**
     * قواعد التحقق من صحة البيانات.
     */
    public function rules()
    {
        return [
            // 1. التحقق من مصفوفة العملاء
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'exists:customer_licenses,id', // يجب أن تكون الأرقام موجودة فعلاً في قاعدة البيانات

            // 2. التحقق من نوع الإجراء
            'action' => 'required|string|in:grant_unlimited,grant_limited,revoke_access',

            // 3. التحقق من التواريخ (السر هنا!)
            // هذا الحقل مطلوب "فقط" إذا كان الإجراء هو grant_limited
            'valid_from' => 'required_if:action,grant_limited|nullable|date',

            // هذا الحقل مطلوب "فقط" إذا كان الإجراء grant_limited، ويجب أن يكون تاريخاً "بعد" تاريخ البداية
            'valid_until' => 'required_if:action,grant_limited|nullable|date|after:valid_from',
        ];
    }

    /**
     * (اختياري) تخصيص رسائل الخطأ لتكون أوضح للواجهة الأمامية
     */
    public function messages()
    {
        return [
            'valid_until.after' => 'تاريخ النهاية يجب أن يكون بعد تاريخ البداية.',
            'valid_from.required_if' => 'تاريخ البداية مطلوب عند اختيار صلاحية محدودة.',
            'valid_until.required_if' => 'تاريخ النهاية مطلوب عند اختيار صلاحية محدودة.',
            'customer_ids.required' => 'يجب تحديد عميل واحد على الأقل.',
        ];
    }
}
