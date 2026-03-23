<?php

namespace Modules\CustomerManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerAccountRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'exists:customer_licenses,id',

            // القائمة الكاملة للإجراءات كما في الصورة
            'action' => 'required|string|in:suspend,activate,delete,grant_publication,grant_document,grant_web_viewer,revoke_web_viewer,resend_license,resend_web_login',

            // اشتراط إرسال المعرفات عند المنح
            'publication_id' => 'required_if:action,grant_publication|exists:publications,id',
            'document_id' => 'required_if:action,grant_document|exists:documents,id',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}

