<?php

namespace Modules\SaaSAdmin\Http\Controllers;

use App\Http\Controllers\Controller;
use Modules\SaaSAdmin\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // عملية تسجيل الدخول الفعلية
    public function login(Request $request)
    {
        // 1. التحقق من المدخلات
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. البحث عن الموظف في قاعدة البيانات
        // أضفنا هذا السطر لكي يفهم VS Code أن المتغير هو موديل وليس كائناً عادياً
        /** @var \Modules\SaaSAdmin\Models\Admin $admin */
        $admin = Admin::where('email', $request->email)->first();

        // 3. التأكد من وجود الموظف وصحة كلمة المرور
        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json([
                'message' => 'بيانات الدخول غير صحيحة.'
            ], 401);
        }

        // 4. التأكد من أن حساب الموظف غير موقوف (is_active)
        if (!$admin->is_active) {
            return response()->json([
                'message' => 'حسابك موقوف، يرجى مراجعة الإدارة العليا.'
            ], 403);
        }

        // 5. توليد الـ Token الحقيقي (Sanctum)
        $token = $admin->createToken('AdminAccessToken')->plainTextToken;

        // 6. إرسال الـ Token للواجهة الأمامية
        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'role' => $admin->role,
            ],
            'token' => $token
        ], 200);
    }

    // عملية تسجيل الخروج (حذف الـ Token)
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح'
        ]);
    }
}
