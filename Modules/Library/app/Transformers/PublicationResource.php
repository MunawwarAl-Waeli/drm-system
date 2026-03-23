<?php

namespace Modules\Library\App\Transformers;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PublicationResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'obey' => $this->obey ? 'yes' : 'no',
            // نجلب الإحصائيات (سنجهزها في الكنترولر باستخدام withCount)
            'customers_count' => $this->licenses_count ?? 0,
            'documents_count' => $this->documents_count ?? 0,
            // تنسيق التاريخ كما في الصورة الشهر-اليوم-السنة
            'date_added' => Carbon::parse($this->created_at)->format('m-d-Y'),
        ];
    }
}
