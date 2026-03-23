<?php

namespace Modules\CustomerManagement\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\CustomerManagement\Services\PublicationCustomersService;
use Modules\CustomerManagement\Transformers\PublicationCustomersResource;
use Modules\CustomerManagement\Http\Requests\BulkActionRequest;
use Modules\Library\Models\Publication;

class PublicationAccessController extends Controller
{
    protected $accessService;

    public function __construct(PublicationCustomersService $accessService)
    {
        $this->accessService = $accessService;
    }

    /**
     * نقطة النهاية (API) لعرض قائمة العملاء للمنشور مع جميع الفلاتر
     */
    public function index(Request $request, Publication $publication)
    {
        // 1. التقاط جميع المتغيرات من الرابط (Query Parameters) مع وضع قيم افتراضية
        $search = $request->query('search', null);               // مربع البحث
        $accessFilter = $request->query('show', 'with_access');        // عرض (الكل، مع صلاحية، بدون)
        $sortBy = $request->query('sort_by', 'name');            // عمود الترتيب
        $sortDirection = $request->query('sort_dir', 'asc');            // تصاعدي/تنازلي

        // التقاط عدد الصفحات (نتأكد أنه رقم، وإذا كان أكثر من 1000 نحدده لحماية السيرفر)
        $perPageRaw = $request->query('per_page', 25);
        $perPage = is_numeric($perPageRaw) ? min((int) $perPageRaw, 1000) : 25;

        // 2. تمرير البيانات للخدمة لمعالجتها
        $customers = $this->accessService->getFilteredCustomers(
            $publication,
            $search,
            $accessFilter,
            $sortBy,
            $sortDirection,
            $perPage
        );

        // 3. إرجاع النتيجة للواجهة عبر الـ Resource لتوحيد شكل البيانات
        return PublicationCustomersResource::collection($customers);
    }

    /**
     * نقطة النهاية (API) لتنفيذ الأوامر الجماعية
     */
    public function bulkAction(BulkActionRequest $request, Publication $publication)
    {
        // البيانات هنا نظيفة وآمنة بفضل BulkActionRequest

        $this->accessService->executeBulkAction(
            $publication,
            $request->validated('customer_ids'),
            $request->validated('action'),
            $request->validated('valid_from'),
            $request->validated('valid_until')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تطبيق التعديلات بنجاح على العملاء المحددين.'
        ]);
    }
}
