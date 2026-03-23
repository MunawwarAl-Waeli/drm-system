<?php
namespace Modules\CustomerManagement\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\CustomerManagement\Services\DocumentCustomersService;
use Modules\CustomerManagement\Transformers\DocumentCustomersResource;
use Modules\CustomerManagement\Http\Requests\BulkActionRequest; // استخدم نفس الـ Request السابق
use Modules\Library\Models\Document;

class DocumentCustomersController extends Controller
{
    protected $accessService;

    public function __construct(DocumentCustomersService $accessService)
    {
        $this->accessService = $accessService;
    }

    public function index(Request $request, Document $document)
    {
        $search = $request->query('search', null);
        $accessFilter = $request->query('show', 'with_access');
        $sortBy = $request->query('sort_by', 'name');
        $sortDirection = $request->query('sort_dir', 'asc');

        $perPageRaw = $request->query('per_page', 25);
        $perPage = is_numeric($perPageRaw) ? min((int) $perPageRaw, 1000) : 25;

        $customers = $this->accessService->getFilteredCustomers(
            $document,
            $search,
            $accessFilter,
            $sortBy,
            $sortDirection,
            $perPage
        );

        return DocumentCustomersResource::collection($customers);
    }

    public function bulkAction(BulkActionRequest $request, Document $document)
    {
        $this->accessService->executeBulkAction(
            $document,
            $request->validated('customer_ids'),
            $request->validated('action'),
            $request->validated('valid_from'),
            $request->validated('valid_until')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تطبيق التعديلات بنجاح على العملاء المحددين للمستند.'
        ]);
    }
}
?>
