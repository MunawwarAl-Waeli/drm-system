<?php

namespace Modules\ReaderApp\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class LicensePayloadResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'publisher_info' => [
                'publisher_id' => $this->publisher_id,
                'name' => $this->company ?? $this->publisher->name ?? 'ناشر غير معروف',
                'support_email' => $this->publisher->email ?? '',
            ],
            'license_info' => [
                'license_id' => $this->id,
                'customer_name' => $this->name,
                'customer_email' => $this->email,
                'max_devices' => $this->max_devices,
                'status' => $this->status,
                'valid_from' => $this->valid_from ? Carbon::parse($this->valid_from)->format('Y-m-d H:i:s') : null,
                'valid_until' => $this->never_expires ? null : ($this->valid_until ? Carbon::parse($this->valid_until)->format('Y-m-d H:i:s') : null),
            ],
            'publications' => PublicationResource::collection($this->publications),
            'documents' => DocumentResource::collection($this->all_processed_documents),
        ];
    }
}
