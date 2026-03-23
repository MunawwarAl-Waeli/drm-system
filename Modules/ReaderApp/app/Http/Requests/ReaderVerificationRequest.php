<?php

namespace Modules\ReaderApp\Http\Requests;



class ReaderVerificationRequest extends BaseReaderRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'hardware_id' => 'required|string|max:255',
            'license_id' => 'required|integer|exists:customer_licenses,id',
            'document_uuid' => 'required|integer|exists:documents,document_uuid',
            'publication_id' => 'nullable|integer',
        ];
    }
}
