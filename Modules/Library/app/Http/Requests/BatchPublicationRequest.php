<?php

namespace Modules\Library\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchPublicationRequest extends FormRequest
{
    public function authorize()
    {
        return true; // تأكد من إضافة صلاحيات الناشر هنا
    }

    public function rules()
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:publications,id'],
            // الإجراءات المنطقية لهذه النافذة
            'action' => ['required', 'in:delete,enable_obey,disable_obey'],
        ];
    }
}
