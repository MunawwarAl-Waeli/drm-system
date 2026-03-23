<?php
    namespace Modules\Library\App\Http\Controllers;



use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Library\App\Http\Requests\BatchProtectedDocumentRequest;
use Modules\Library\App\Services\ProtectedDocumentService;
use Modules\Library\App\Transformers\ProtectedDocumentDetailResource;
use Modules\Library\App\Transformers\ProtectedDocumentResource;
use Modules\Library\Models\Document;

class ProtectedDocumentController extends Controller
{
    protected $documentService;

    public function __construct(ProtectedDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * عرض المستندات المحمية (تطابق الصورة تماماً)
     */
    public function index(Request $request)
    {
        // جلب الناشر الحالي
        $publisherId = auth()->user()->publisher_id;

        $documents = Document::where('publisher_id', $publisherId)
            // ترتيب تنازلي بناءً على تاريخ النشر كما هو معتاد
            ->orderBy('published_at', 'desc')
            ->paginate($request->get('show_at_least', 25));

        return ProtectedDocumentResource::collection($documents);
    }

    /**
     * الإجراءات الجماعية (With all checked) أو الفردية (الأيقونات)
     * يمكن استخدام هذا الراوت للمفرد والجماعي بإرسال مصفوفة فيها ID واحد
     */
    public function batchAction(BatchProtectedDocumentRequest $request)
    {
        $publisherId = auth()->user()->publisher_id;

        $this->documentService->handleBatchAction(
            $request->validated('ids'),
            $request->validated('action'),
            $publisherId
        );

        return response()->json([
            'message' => 'The selected action was successfully applied to the documents.'
        ]);
    }


    public function show($id)
    {
        $publisherId = auth()->user()->publisher_id;

        // جلب المستند مع إعدادات الأمان الخاصة به
        $document = Document::with('securityControls')
            ->where('id', $id)
            ->where('publisher_id', $publisherId) // أمان: للتأكد أنه يتبع لهذا الناشر
            ->firstOrFail();

        return new ProtectedDocumentDetailResource($document);
    }

    /**
     * تحديث ملاحظات المستند (الزر الأزرق Save في أسفل الصورة)
     */
    public function updateNotes(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string'
        ]);

        $publisherId = auth()->user()->publisher_id;

        $document = Document::where('id', $id)
            ->where('publisher_id', $publisherId)
            ->firstOrFail();

        // سنقوم بحفظ الملاحظات في حقل description
        $document->update([
            'description' => $request->notes
        ]);

        return response()->json([
            'message' => 'Document notes updated successfully.'
        ]);
    }
}
?>

