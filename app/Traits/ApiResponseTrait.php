<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function sendResponse(bool $success, string $action, string $message, $data = null, int $code = 200)
    {
        return response()->json([
            'success' => $success,
            'error_code' => $action,
            'message' => $message,
            'data' => $data
        ], $code);
    }
}
