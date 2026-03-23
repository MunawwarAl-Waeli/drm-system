<?php

namespace Modules\CustomerManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerBulkActionRequest extends FormRequest
{
    public function authorize()
    {
        return true; // بناء الصلاحيات لاحقاً
    }

    public function rules()
    {
        return [
            'ids' => ['required', 'array', 'min:1'], // أرقام المنشورات أو المستندات المحددة
            'ids.*' => ['integer'],
            'action' => ['required', 'in:grant_unlimited,grant_limited,revoke_access'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];
    }
}
