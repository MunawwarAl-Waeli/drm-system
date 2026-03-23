<?php

namespace Modules\SaaSAdmin\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
// use Modules\SaaSAdmin\Actions\VerifyWriterAuthAction;
use Exception;

class WriterApiController extends Controller
{
    public function verify(Request $request, VerifyWriterAuthAction $action)
    {
        $request->validate([
            'license_id' => 'required|integer',
            'payload' => 'required|string', // النص المشفر
        ]);

        try {
            $result = $action->execute($request->license_id, $request->payload);

            // هنا يمكنك إضافة دالة تشفير للرد لكي لا يقرأه الهاكر (باستخدام Inner Key)
            return response()->json([
                'success' => true,
                'data' => $result
            ]);

        } catch (Exception $e) {
            // نرجع 401 للبرنامج في حال التزوير أو الخطأ
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }
}
