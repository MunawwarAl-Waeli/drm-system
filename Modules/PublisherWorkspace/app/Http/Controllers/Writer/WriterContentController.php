<?php

namespace Modules\PublisherWorkspace\Http\Controllers\Writer;

use App\Http\Controllers\Controller; // تم التعديل ليتطابق مع الكنترولر الأول
use Illuminate\Http\Request;
use Modules\Library\Models\Publication;
use Modules\PublisherWorkspace\Http\Requests\SyncWriterDocumentsRequest;
use Modules\Library\Services\WriterSyncService;
use App\Traits\ApiResponseTrait;
class WriterContentController extends Controller
{
    use ApiResponseTrait; // 2. استخدام الترايت داخل الكلاس

    public function sync(SyncWriterDocumentsRequest $request, WriterSyncService $syncService)
    {
        // تم التحقق بنجاح! نأخذ البيانات النظيفة
        $validatedData = $request->validated();

        $publisherId = $request->user()->id;

        // نمرر البيانات للخدمة في وحدة المكتبة لتقوم بعملها بصمت
        $savedDocsIds = $syncService->syncDocuments($publisherId, $validatedData);


        return $this->sendResponse(
            true,
            'sync_success',
            'تمت مزامنة الملفات بنجاح.',
            $savedDocsIds,
            200
        );
    }

    /**
     * جلب قائمة المنشورات الخاصة بالناشر (لعرضها في برنامج C#)
     */
    public function getPublications(Request $request)
    {
        // 1. جلب رقم الناشر من التوكن
        $publisherId = $request->user()->id;

        // 2. الاستعلام عن المنشورات الخاصة به فقط (من وحدة المكتبة)
        // نختار فقط الحقول التي يحتاجها برنامج الـ C# لتخفيف حجم البيانات
        $publications = Publication::where('publisher_id', $publisherId)
            ->select('id', 'name', 'description')
            ->get();

        $responseData = [
            'count' => $publications->count(),
            'items' => $publications
        ];

        // 3. إرجاع النتيجة كـ JSON موحد
        return $this->sendResponse(
            true,
            'publications',
            'تم جلب المنشورات بنجاح.',
            $responseData,
            200
        );
    }
}
