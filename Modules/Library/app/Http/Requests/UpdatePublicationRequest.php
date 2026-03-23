<?php
namespace Modules\Library\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePublicationRequest extends FormRequest
{
    public function authorize()
    {
        return true; // تأكد من صلاحيات الناشر هنا
    }

    public function rules()
    {
        return [
            // نمنع تعديل الاسم، ونسمح فقط بالوصف وحالة obey
            'description' => ['nullable', 'string', 'max:1000'],
            'obey' => ['required', 'boolean'],
        ];
    }
}
?>