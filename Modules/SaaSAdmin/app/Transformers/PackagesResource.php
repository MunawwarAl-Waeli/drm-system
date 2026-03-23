<?php

namespace Modules\SaaSAdmin\Transformers;

// use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackagesResource extends JsonResource
{

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'price' => $this->base_price,
            'classifications' => [
                'license_type' => $this->license_type,
                'security_grade' => $this->security_grade,
            ],
            'quotas' => [
                'max_documents' => $this->base_max_documents,
                'max_file_size_mb' => $this->base_max_file_size_mb,
                'max_total_storage_mb' => $this->base_max_total_storage_mb,
                'batch_size' => $this->base_batch_size,
                'devices_allowed' => $this->base_devices_allowed,
            ],
            'content_policies' => [
                'allowed_extensions' => $this->allowed_extensions ?? [],
            ],
            'features' => [
                'can_use_guest_link' => $this->can_use_guest_link,
                'can_use_dynamic_watermark' => $this->can_use_dynamic_watermark,
                'allow_custom_splash_screen' => $this->allow_custom_splash_screen,
                'remove_vendor_watermark' => $this->remove_vendor_watermark,
            ],
            'is_active' => $this->is_active,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }

}
