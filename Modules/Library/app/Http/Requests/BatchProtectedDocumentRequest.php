<?php
namespace Modules\Library\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchProtectedDocumentRequest extends FormRequest
{
    public function authorize()
    {
        return true; // تأكد من صلاحيات الناشر
    }

    public function rules()
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:documents,id'],

            // الإجراءات المتاحة لهذه النافذة بناءً على LockLizard
            'action' => ['required', 'in:suspend,activate,delete'],
        ];
    }
}
?>