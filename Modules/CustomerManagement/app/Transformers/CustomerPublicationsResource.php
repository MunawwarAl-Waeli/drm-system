<?php

namespace Modules\CustomerManagement\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CustomerAccessPublicationResource extends JsonResource
{
    public function toArray($request)
    {
        $accessText = 'No';

        // جلب الرخصة من العلاقة المحملة مسبقاً (pivot)
        $license = $this->customerlicenses->first();

        if ($license && $license->pivot && $license->pivot->status === 'active') {
            $accessText = $license->pivot->access_mode === 'unlimited'
                ? 'unlimited'
                : ($license->pivot->valid_from ? Carbon::parse($license->pivot->valid_from)->format('m-d-Y') : '...') . ' to ' .
                ($license->pivot->valid_until ? Carbon::parse($license->pivot->valid_until)->format('m-d-Y') : '...');
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'obey' => $this->obey ? 'yes' : 'no',
            'ui_access' => $accessText,
        ];
    }
}
