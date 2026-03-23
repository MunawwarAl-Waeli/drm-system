<?php

namespace Modules\ReaderApp\Http\Requests;



class LoginReaderRequest extends BaseReaderRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required|string',

            // بيانات الجهاز المطلوبة أيضاً هنا! (لأنه قد يسجل دخول من لابتوب جديد)
            'device_info' => 'required|array',
            'device_info.hardware_id' => 'required|string|max:255',
            'device_info.device_name' => 'required|string|max:255',
            'device_info.device_type' => 'required|string|max:100',
            'device_info.os_version' => 'required|string|max:100',
        ];
    }
}
