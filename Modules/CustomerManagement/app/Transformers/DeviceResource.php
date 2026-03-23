<?php
namespace Modules\CustomerManagement\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class DeviceResource extends JsonResource
{
    public function toArray($request)
    {
        $devices = $this->devices->first();
        $accessText = "";
        if ($devices && $devices->pivot) {
            $accessText = $devices->pivot->status === 'active' ? "enabled" : "suspend";
        }
        return [
            'id' => $this->id,
            'machine_id' => $this->hardware_id,
            'device_name' => $this->device_name,
            'status' => $accessText,
            'last_used' => $this->last_synced_at ? $this->last_synced_at->format('m-d-Y H:i') : 'Never',
        ];
    }
}
