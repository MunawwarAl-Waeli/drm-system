<?php

namespace Modules\Library\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetPublicationsRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // هذا الحقل تم حقنه بواسطة الـ Middleware
            'publisher_id' => 'required|exists:publishers,id',
        ];
    }
}
