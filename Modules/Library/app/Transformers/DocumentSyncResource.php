<?php

namespace Modules\Library\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DocumentSyncResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            // نرجع له الـ UUID لكي يتأكد الـ C# أن هذا الملف بالذات تم حفظه
            'document_uuid' => $this->document_uuid,
            'title' => $this->title,
            'status' => $this->status,
            'publication' => $this->publication ? $this->publication->name : 'مستقل (بدون منشور)',

            // رسالة طمأنينة للكاتب
            'message' => 'تم مزامنة الملف وحفظ مفتاحه وقيوده بنجاح',
            'synced_at' => now()->format('Y-m-d H:i:s'),
        ];
    }
}
