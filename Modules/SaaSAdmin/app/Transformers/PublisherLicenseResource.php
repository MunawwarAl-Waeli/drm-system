<?php

namespace Modules\SaaSAdmin\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PublisherLicenseResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'package_name' => $this->package->name ?? 'Unknown',

            // السحر هنا: نرسل القيود الفعلية (سواء من الباقة أو الاستثناء)
            'actual_quotas' => [
                'max_documents' => $this->actual_max_documents,
                'max_file_size' => $this->actual_max_file_size_mb . ' MB',
                'total_storage' => $this->actual_max_total_storage_mb . ' MB',
                'batch_size' => $this->actual_batch_size,
                'devices_allowed' => $this->actual_devices_allowed,
            ],

            'is_customized' => $this->custom_max_documents !== null, // لمعرفة هل لديه استثناء أم لا

            'starts_at' => $this->starts_at ? $this->starts_at->format('Y-m-d') : null,
            'expires_at' => $this->expires_at ? $this->expires_at->format('Y-m-d') : null,
        ];
    }
}
