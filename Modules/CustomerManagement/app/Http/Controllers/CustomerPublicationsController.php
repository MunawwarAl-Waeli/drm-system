<?php

namespace Modules\CustomerManagement\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\CustomerManagement\Services\CustomerPublicationAccessService;
use Modules\CustomerManagement\Transformers\CustomerAccessPublicationResource;
use Modules\CustomerManagement\Http\Requests\CustomerBulkActionRequest;

class CustomerPublicationsController extends Controller
{
    protected $accessService;

    public function __construct(CustomerPublicationAccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    public function index(Request $request, CustomerLicense $license)
    {
        $perPageRaw = $request->query('per_page', 25);
        $perPage = is_numeric($perPageRaw) ? min((int) $perPageRaw, 1000) : 25;

        // استدعاء الخدمة لجلب المنشورات الخاصة بهذا العميل
        $publications = $this->accessService->getCustomerPublications($license, $perPage);

        return CustomerAccessPublicationResource::collection($publications);
    }

    public function bulkAction(CustomerBulkActionRequest $request, CustomerLicense $license)
    {
        $this->accessService->executeBulkAction(
            $license,
            $request->validated('ids'),
            $request->validated('action'),
            $request->validated('valid_from'),
            $request->validated('valid_until')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تطبيق الصلاحيات بنجاح على المنشورات المحددة.'
        ]);
    }
}
