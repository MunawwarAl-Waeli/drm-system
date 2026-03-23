<?php


namespace Modules\CustomerManagement\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\CustomerManagement\Http\Requests\UpdateCustomerAccountRequest;
use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\CustomerManagement\Services\CustomerAccountService;
use Modules\CustomerManagement\Transformers\CustomerAccountResource;
use Modules\CustomerManagement\Http\Requests\CustomerAccountRequest;

class CustomerAccountController extends Controller
{
    protected $customerService;

    public function __construct(CustomerAccountService $customerService)
    {
        $this->customerService = $customerService;
    }

    // 1.  عرض البيانات كامله
    public function index()
    {
        $customers = $this->customerService->getCustomers();
        return CustomerAccountResource::collection($customers);
    }

    // 2. تنفيذ الإجراءات الجماعية
    public function bulkAction(CustomerAccountRequest $request)
    {
        $this->customerService->executeBulkAction(
            $request->validated('customer_ids'),
            $request->validated('action'),
            $request->validated('publication_id'),
            $request->validated('document_id')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'تم تنفيذ الإجراء بنجاح على العملاء المحددين.'
        ]);
    }

    /**
     * جلب تفاصيل عميل محدد (عند النقر على اسمه)
     */
    public function show($id)
    {
        $customer = CustomerLicense::findOrFail($id);
        return new CustomerAccountResource($customer);
    }

    /**
     * تحديث بيانات العميل (بما فيها إعدادات الـ IP والدولة)
     */
    public function update(UpdateCustomerAccountRequest $request, $id)
    {
        $customer = CustomerLicense::findOrFail($id);

        // التحديث بكل البيانات الموثوقة التي جاءت من الـ Request
        $customer->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'تم تحديث بيانات العميل بنجاح.',
            // نرجع البيانات الجديدة محدثة للواجهة
            'data' => new CustomerAccountResource($customer)
        ]);
    }
}



