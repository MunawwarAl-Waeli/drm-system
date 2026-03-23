<?php

namespace Modules\ReaderApp\Http\Requests;



class ReaderActivationRequest extends BaseReaderRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hardware_id' => 'required|string|max:255',
            'activation_type' => 'required|in:license_file,voucher',
            'license_id' => 'required_if:activation_type,license_file|integer|exists:customer_licenses,id',
            'voucher_code' => 'required_if:activation_type,voucher|string|exists:vouchers,code',
        ];
    }

    public function messages(): array
    {
        return [
            'license_id.exists' => 'ملف الرخصة غير صالح أو محذوف من النظام.',
            'voucher_code.exists' => 'كود التفعيل غير صحيح أو غير موجود.',
        ];
    }
}
