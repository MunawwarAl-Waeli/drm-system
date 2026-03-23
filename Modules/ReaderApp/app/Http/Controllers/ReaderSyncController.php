<?php

namespace Modules\ReaderApp\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\ReaderApp\Http\Requests\ReaderSyncRequest;
use Modules\ReaderApp\Services\ReaderSyncService;
use App\Traits\ApiResponseTrait; // 👈 استدعاء صانع الغلاف الموحد

class ReaderSyncController extends Controller
{
    use ApiResponseTrait; // 👈 تفعيل الغلاف الموحد

    protected $syncService;

    public function __construct(ReaderSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    public function syncContent(ReaderSyncRequest $request)
    {
        $reader = $request->user();

        // 1. تنفيذ الخدمة (والتي سترجع كائن الـ Resource أو مصفوفة خطأ)
        $result = $this->syncService->syncContent($reader, $request->validated());

        // 2. إذا فشل الفحص الأمني (الجهاز محظور أو الرخصة غير موجودة)
        if (is_array($result) && isset($result['success']) && !$result['success']) {
            return $this->sendResponse(false, 'sync_failed', $result['message'], null, 403);
        }

        // 3. إذا نجحت الخدمة (يوجد تحديثات أو لا يوجد)
        $hasUpdates = $result->resource->has_any_updates;
        $action = $hasUpdates ? 'sync_completed' : 'sync_up_to_date';
        $message = $hasUpdates ? 'تم جلب التحديثات بنجاح.' : 'محتواك محدث لآخر نسخة.';

        // 4. إرجاع الرد المغلف بشكل موحد!
        // result->resolve() ستقوم بإرجاع المصفوفة الصافية من SyncPayloadResource وتضعها في حقل data
        return $this->sendResponse(true, $action, $message, $result->resolve());
    }
}
