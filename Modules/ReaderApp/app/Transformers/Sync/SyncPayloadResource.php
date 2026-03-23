<?php

namespace Modules\ReaderApp\Transformers\Sync;

use Illuminate\Http\Resources\Json\JsonResource;

class SyncPayloadResource extends JsonResource
{
    public function toArray($request)
    {
        // نُرجع محتوى البيانات فقط
        return [
            'has_updates' => $this->has_any_updates,
            'new_sync_date' => now()->format('Y-m-d H:i:s'),
            'updates' => [
                'license' => $this->sync_license_updates,
                'publications' => SyncPublicationResource::collection($this->sync_publications),
                'documents' => SyncDocumentResource::collection($this->sync_documents),
            ]
        ];
    }
}
