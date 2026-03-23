<?php

namespace Modules\ReaderApp\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * هذا هو "الأب" الذي سترث منه كل طلبات تطبيق القارئ
 */
abstract class BaseReaderRequest extends FormRequest
{
    /**
     * اعتراض أخطاء التحقق (Validation) وتغليفها بالغلاف الموحد
     */
    protected function failedValidation(Validator $validator)
    {
        // نجلب أول رسالة خطأ فقط لنعرضها للمستخدم بشكل نظيف
        $firstError = $validator->errors()->first();

        // نرمي الخطأ بالغلاف القياسي الذي اتفقنا عليه
        throw new HttpResponseException(response()->json([
            'success' => false,
            'action' => 'validation_error',
            'message' => $firstError,
            'data' => $validator->errors() // نضع كل الأخطاء هنا في حال احتاجها مبرمج التطبيق
        ], 422));
    }
}
