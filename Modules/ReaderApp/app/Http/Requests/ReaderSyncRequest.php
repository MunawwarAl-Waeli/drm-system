<?php

namespace Modules\ReaderApp\Http\Requests;



class ReaderSyncRequest extends BaseReaderRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hardware_id' => 'required|string|max:255',
            'license_id' => 'required|integer|exists:customer_licenses,id', // 👈 أضفنا فحص الوجود لحماية قاعدة البيانات
            'last_sync_date' => 'required|date', // 👈 قمنا بتخفيف القيود لتقبل أي صيغة تاريخ صالحة
        ];
    }
}
