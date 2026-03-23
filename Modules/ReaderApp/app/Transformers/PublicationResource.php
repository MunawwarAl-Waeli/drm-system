<?php

namespace Modules\ReaderApp\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PublicationResource extends JsonResource
{
    public function toArray($request)
    {
        $pivot = $this->pivot;
        $access = $this->evaluated_access;

        $accessMode = null;
        $validUntil = null;

        if ($pivot->access_mode == "limited") {
            $accessMode = "fixed_date";
            $validUntil = $pivot->valid_until ? Carbon::parse($pivot->valid_until)->format('Y-m-d H:i:s') : null;
        } elseif ($pivot->access_mode == "unlimited") {
            $accessMode = "never";
        }

        return [
            'publication_id' => $this->id,
            'status' => $access['status'],
            'message' => $access['reason'],
            'data' => $access['is_accessible'] ? [
                'name' => $this->name,
                'access_mode' => $accessMode,
                'valid_until' => $validUntil,
            ] : null
        ];
    }
}
