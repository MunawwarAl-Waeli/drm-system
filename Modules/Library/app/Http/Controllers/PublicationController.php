<?php
    namespace Modules\Library\App\Http\Controllers;



use Illuminate\Http\Request;
use Modules\Library\App\Http\Requests\BatchPublicationRequest;
use Modules\Library\App\Http\Requests\UpdatePublicationRequest;
use Modules\Library\App\Services\PublicationService;
use Modules\Library\App\Transformers\PublicationResource;
use Modules\Library\Models\Publication;
use Illuminate\Routing\Controller;
class PublicationController extends Controller
{
    protected $publicationService;

    public function __construct(PublicationService $publicationService)
    {
        $this->publicationService = $publicationService;
    }

    /**
     * عرض المنشورات (تطابق الصورة تماماً)
     */
    public function index(Request $request)
    {
        // افترض أننا نحصل على معرف الناشر من المستخدم المسجل دخول
        $publisherId = auth()->user()->publisher_id; // عدلها حسب اللوجيك الخاص بك

        $publications = Publication::where('publisher_id', $publisherId)
            // سحر لارافل: يجلب الإحصائيات كأعمدة إضافية (documents_count و licenses_count)
            ->withCount(['documents', 'customerlicense'])
            ->orderBy('created_at', 'desc')
            ->paginate($request->get('show_at_least', 25));

        return PublicationResource::collection($publications);
    }

    /**
     * الإجراءات الجماعية (With all checked)
     */
    public function batchAction(BatchPublicationRequest $request)
    {
        $publisherId = auth()->user()->publisher_id; // عدلها حسب اللوجيك الخاص بك

        $this->publicationService->handleBatchAction(
            $request->validated('ids'),
            $request->validated('action'),
            $publisherId
        );

        return response()->json(['message' => 'Action applied successfully on selected publications.']);
    }
    /**
     * تحديث بيانات منشور معين (الوصف والـ Obey فقط)
     */
    public function update(UpdatePublicationRequest $request, Publication $publication)
    {
        // تحقق أمني: التأكد من أن المنشور يتبع للناشر الحالي
        if ($publication->publisher_id !== auth()->user()->publisher_id) {
            abort(403, 'Unauthorized action.');
        }

        $publication->update($request->only(['description', 'obey']));

        return response()->json([
            'message' => 'Publication updated successfully',
            'data' => new PublicationResource($publication)
        ]);
    }
}
?>
