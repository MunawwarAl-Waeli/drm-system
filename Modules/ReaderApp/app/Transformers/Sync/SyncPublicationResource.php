<?php

namespace Modules\ReaderApp\Transformers\Sync;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class SyncPublicationResource extends JsonResource
{
    public function toArray($request)
    {
        $pivot = $this->pivot;
        $access = $this->evaluated_access;
        $isNew = $this->is_new_sync;

        if (!$access['is_accessible']) {
            return [
                'publication_id' => $this->id,
                'sync_action' => ($pivot->status === 'revoked') ? 'revoke' : 'suspend', // توحيد اسم المفتاح
                'status' => $pivot->status,
                'message' => $access['reason'],
                'data' => null
            ];
        }

        $accessMode = $pivot->access_mode === "unlimited" ? "never" : "fixed_date";
        $validUntil = $pivot->valid_until ? Carbon::parse($pivot->valid_until)->format('Y-m-d H:i:s') : null;

        return [
            'publication_id' => $this->id,
            'sync_action' => $isNew ? 'add' : 'update', // توحيد اسم المفتاح
            'status' => 'active',
            'message' => $isNew ? 'تمت إضافة كورس جديد.' : 'تم تحديث بيانات الكورس.',
            'data' => [
                'name' => $this->name,
                'access_mode' => $accessMode,
                'valid_until' => $validUntil,
            ]
        ];
    }
}
