<?php

namespace Modules\PublisherWorkspace\Http\Controllers\Publisher;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Modules\PublisherWorkspace\Models\Publisher;

class PublisherPanelAuthController extends Controller
{
    // 1. تسجيل الدخول للوحة التحكم (الويب)
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $publisher = Publisher::where('email', $request->email)->first();

        // فحص صحة البيانات
        if (!$publisher || !Hash::check($request->password, $publisher->password)) {
            return response()->json(['success' => false, 'message' => 'بيانات الدخول غير صحيحة'], 401);
        }

        // فحص حالة الحساب
        if ($publisher->status !== 'active') {
            return response()->json(['success' => false, 'message' => 'حسابك غير نشط، يرجى مراجعة الدعم الفني'], 403);
        }

        // إصدار التوكن المخصص للوحة التحكم فقط
        $token = $publisher->createToken(
            'PanelApp_' . $request->device_name,
            ['panel-access'] // <--- صلاحية لوحة التحكم
        )->plainTextToken;

        return response()->json([
            'success' => true,
            'token' => $token,
            'publisher' => $publisher->only(['id', 'name', 'email', 'company'])
        ], 200);
    }

    // 2. تسجيل الخروج من لوحة التحكم
    public function logout(Request $request)
    {
        // حذف التوكن الحالي الذي يستخدمه المتصفح
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الخروج من لوحة التحكم بنجاح'
        ], 200);
    }
}
