<?php

namespace Modules\CustomerManagement\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\CustomerManagement\Services\CustomerDocumentAccessService;
use Modules\CustomerManagement\Transformers\CustomerAccessDocumentResource;
use Modules\CustomerManagement\Http\Requests\CustomerBulkActionRequest; // استخدمنا نفس الريكويست

class CustomerDocumentsController extends Controller
{
    protected $accessService;

    public function __construct(CustomerDocumentAccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    /**
     * جلب قائمة المستندات مع حالة وصول العميل لها
     */
    public function index(Request $request, CustomerLicense $license)
    {
        $perPageRaw = $request->query('per_page', 25);
        $perPage = is_numeric($perPageRaw) ? min((int) $perPageRaw, 1000) : 25;

        // تسليم المهمة للخدمة
        $documents = $this->accessService->getCustomerDocuments($license, $perPage);

        // إرجاع البيانات مغلفة بـ Resource لتطابق الواجهة
        return CustomerAccessDocumentResource::collection($documents);
    }

    /**
     * تنفيذ الإجراء الجماعي (منح/سحب صلاحيات) على المستندات
     */
    public function bulkAction(CustomerBulkActionRequest $request, CustomerLicense $license)
    {
        // إرسال البيانات (الموجودة في الـ Body) للخدمة لتنفيذها
        $this->accessService->executeBulkAction(
            $license,
            $request->validated('ids'),
            $request->validated('action'),
            $request->validated('valid_from'),
            $request->validated('valid_until')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تطبيق الصلاحيات بنجاح على المستندات المحددة.'
        ]);
    }
}
