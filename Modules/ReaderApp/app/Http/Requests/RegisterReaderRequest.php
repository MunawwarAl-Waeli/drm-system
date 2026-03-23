<?php

namespace Modules\ReaderApp\Http\Requests;


class RegisterReaderRequest extends BaseReaderRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:readers,email|max:255',
            'password' => 'required|string|min:6|confirmed', // 👈 أضفنا confirmed لمطابقة كلمة المرور


            // ... قواعد المستخدم ...

            'device_info' => 'required|array',
            'device_info.hardware_id' => 'required|string|max:255',
            'device_info.device_name' => 'required|string|max:255',
            'device_info.device_type' => 'required|string|max:100',
            'device_info.os_version'  => 'required|string|max:100',

        ];
    }
}
