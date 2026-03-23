<?php

namespace Modules\PublisherWorkspace\Http\Requests;



class RegisterPublisherRequest extends BaseReaderRequest
{
    public function authorize()
    {
        return true; // مسموح للجميع (لأنه مسار تسجيل جديد)
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:publishers,email',
            'password' => 'required|string|min:8|confirmed', // يتطلب إرسال password_confirmation
            'phone' => 'nullable|string|max:20',
            'company' => 'required|string|max:255',
            'country' => 'nullable|string|max:100',
        ];
    }

    public function messages()
    {
        return [
            'email.unique' => 'هذا البريد الإلكتروني مسجل مسبقاً في النظام.',
            'password.confirmed' => 'كلمات المرور غير متطابقة.',
        ];
    }
}
