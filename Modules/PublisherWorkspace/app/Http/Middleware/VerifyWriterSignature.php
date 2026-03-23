<?php

namespace Modules\PublisherWorkspace\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\PublisherWorkspace\Services\LicenseFactoryService;

class VerifyWriterSignature
{
    protected $licenseFactory;

    public function __construct(LicenseFactoryService $licenseFactory)
    {
        $this->licenseFactory = $licenseFactory;
    }

    public function handle(Request $request, Closure $next)
    {
        // 1. جلب الناشر المسجل دخوله
        $publisher = $request->user();
        // $publisher =1;

        // 2. جلب رخصته لمعرفة المفتاح العام
        $license = $publisher->licenses()->where('status', 'active')->first();

        if (!$license || !$license->public_certificate) {
            return response()->json(['message' => 'لا تملك رخصة نشطة لإرسال الطلبات.'], 403);
        }

        try {
            // 3. أخذ البيانات المشفرة التي أرسلها برنامج C#
            $encryptedPayload = $request->input('payload');

            // 4. فك التشفير باستخدام الدالة التي برمجناها سابقاً
            $decryptedData = $this->licenseFactory->decryptAndVerifyWriterRequest(
                $encryptedPayload,
                $license->public_certificate
            );

            // 5. استبدال الطلب المشفر بالبيانات المكشوفة!
            // (الآن الكنترولر سيراها كأنها لم تكن مشفرة أبداً)
            $request->replace($decryptedData);

        } catch (\Exception $e) {
            // إذا كان التشفير خاطئاً أو تم التلاعب به، نرفض الطلب
            return response()->json(['message' => 'بيانات غير صالحة أو تم التلاعب بها.'], 403);
        }

        return $next($request);
    }
}
