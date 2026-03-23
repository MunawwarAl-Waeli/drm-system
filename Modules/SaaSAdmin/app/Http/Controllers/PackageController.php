<?php

namespace Modules\SaaSAdmin\Http\Controllers;

use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;
use Modules\SaaSAdmin\Http\Requests\StorePackagesRequest;
use Modules\SaaSAdmin\Models\Package;
use Modules\SaaSAdmin\Transformers\PackagesResource;

class PackageController extends Controller
{

    // عرض كل الباقات (لوحة التحكم)
    public function index()
    {
        $packages = Package::all();
        return PackagesResource::collection($packages);
    }

    // عرض الباقات المفعلة فقط (لواجهة العملاء لشراء باقة)
    public function getActivePackages()
    {
        $packages = Package::where('is_active', true)->get();
        return PackagesResource::collection($packages);
    }

    // إنشاء باقة جديدة
    public function store(StorePackagesRequest $request)
    {

        $data = $request->validated();


        $currentAdmin = auth()->user(); // يجلب بيانات الموظف المسجل دخوله حالياً

        if ($currentAdmin->role === 'billing') {
            return response()->json(['error' => 'عذراً، موظف الحسابات لا يملك صلاحية إضافة باقة.'], 403);
        }

        // 3. إضافة ID الموظف إلى بيانات الباقة
        // هذه هي النقطة السحرية التي تربط الجدولين!


        $package = Package::create($data);

        // 5. إرجاع رسالة نجاح توضح من قام بالعملية
        return response()->json([
            'message' => 'تم إنشاء الباقة بنجاح!',
            'added_by' => $currentAdmin->name, // يعرض اسم الموظف الذي أضافها
            'package_data' => $package
        ], 201);
    }
    // عرض باقة محددة
    public function show(Package $package)
    {
        return new PackagesResource($package);
    }

    // تحديث باقة
    public function update(StorePackagesRequest $request, Package $package)
    {
        $package->update($request->validated());

        return response()->json([
            'message' => 'تم تحديث الباقة بنجاح',
            'data' => new PackagesResource($package)
        ]);
    }

    // تفعيل/تعطيل باقة (Toggle Status)
    public function toggleActive(Package $package)
    {
        $package->update(['is_active' => !$package->is_active]);

        $status = $package->is_active ? 'مفعلة' : 'معطلة';
        return response()->json([
            'message' => "تم تغيير حالة الباقة لتصبح $status"
        ]);
    }

}

