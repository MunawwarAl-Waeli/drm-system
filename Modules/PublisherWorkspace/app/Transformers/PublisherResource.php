<?php

namespace Modules\PublisherWorkspace\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class PublisherResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'company' => $this->company,
            'status' => $this->status,
            'joined_at' => $this->created_at->format('Y-m-d H:i:s'),
            // إذا قمنا بتحميل الرخصة مع الناشر، نعرضها أيضاً
            'active_license' => $this->whenLoaded('licenses', function () {
                $license = $this->licenses->first();
                return $license ? [
                    'key' => $license->key,
                    'package_name' => $license->package->name ?? 'غير معروف',
                    'expires_at' => $license->expires_at->format('Y-m-d'),
                ] : null;
            }),
        ];
    }
}
