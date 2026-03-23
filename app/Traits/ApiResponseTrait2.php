<?php

namespace App\Traits;

trait ApiResponseTrait
{
    /**
     * @param bool   $success حالة النجاح
     * @param string $errorCode كود الخطأ أو الإجراء (مثل: device.NOT_REGISTERED)
     * @param string $messageKey مفتاح الرسالة في ملف الترجمة (أو نص عادي كبديل)
     * @param mixed  $data البيانات الإضافية
     * @param int    $code كود الـ HTTP
     * @param array  $replacements مصفوفة المتغيرات لتمريرها للرسالة (مثل date أو type)
     */
    public function sendResponse(bool $success, string $errorCode, string $messageKey, $data = null, int $code = 200, array $replacements = [])
    {
        // 1. محاولة ترجمة الرسالة باستخدام دالة لارافل
        // دالة __() ستبحث عن المفتاح، وإذا لم تجده ستعرض المفتاح نفسه كنص.
        $message = __($messageKey, $replacements);

        // 2. إرجاع الرد النهائي
        return response()->json([
            'success' => $success,
            'error_code' => $errorCode,
            'message' => $message, // هنا سيظهر النص العربي للمستخدم
            'data' => $data
        ], $code);
    }
}
