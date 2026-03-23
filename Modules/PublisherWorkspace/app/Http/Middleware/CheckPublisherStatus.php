<?php

namespace Modules\PublisherWorkspace\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPublisherStatus
{
    public function handle(Request $request, Closure $next)
    {
        $publisher = $request->user();

        // 1. هل تم حظر حسابه من الإدارة؟
        if ($publisher->status !== 'active') {
            // نقوم بتدمير التوكن الخاص به فوراً
            $publisher->currentAccessToken()->delete();
            return response()->json(['message' => 'تم إيقاف حسابك، يرجى مراجعة الإدارة.'], 403);
        }

        // 2. هل رخصته انتهت؟ (نبحث عن الرخصة المرتبطة بهذا التوكن/الجهاز)
        // لنفترض أننا نجلب الرخصة الفعالة للناشر
        $activeLicense = $publisher->licenses()->where('status', 'active')->first();

        if (!$activeLicense) {
            return response()->json(['message' => 'رخصتك منتهية، يرجى تجديد الاشتراك من لوحة التحكم.'], 402); // 402 Payment Required
        }

        // إذا اجتاز التفتيش، تفضل بالمرور للمسار المطلوب (رفع الملف)
        return $next($request);
    }
}
