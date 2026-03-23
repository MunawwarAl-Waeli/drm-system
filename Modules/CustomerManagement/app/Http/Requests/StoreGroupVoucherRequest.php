<?php

namespace Modules\CustomerManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGroupVoucherRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */

    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        $publisherId = $this->user()->id;
        return [
            'name' => 'required|string|max:255', // اسم الجهة (مثلاً: مكتبة الأمل)
            'email' => 'required|email|max:255', // إيميل الجهة
            'company' => 'nullable|string|max:255',

            // 🌟 الحقل الخاص بالكروت 🌟
            'vouchers_count' => 'required|integer|min:1|max:5000',

            'max_devices' => 'required|integer|min:1', // الأجهزة لكل كرت
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'never_expires' => 'boolean', // يجب أن يرسل الفرونت إند true أو false
                     // تحقق المنشورات والملفات (كما اتفقنا سابقاً)
            'publications' => 'required_without:documents|array',
            'publications.*' => [
                'integer',
                // 'distinct',
                Rule::exists('publications', 'id')->where(function ($query) use ($publisherId) {
                    $query->where('publisher_id', $publisherId);
                }),
            ],
            'documents' => 'required_without:publications|array',
            'documents.*' => [
                'integer',
                 Rule::exists('documents', 'id')->where(function ($query) use ($publisherId) {
                    $query->whereNull('publication_id')
                        ->where(function ($subQuery) use ($publisherId) {
                            $subQuery->where('publisher_id', $publisherId)
                                // تم التعديل ليطابق الحقل الذي أخبرتني عنه
                                ->orWhereIn('access_scope', ['all_customers']);
                        });
                }),
            ],
        ];

    }

    /**
     * Determine if the user is authorized to make this request.
     */

    public function messages()
    {
        return [
            'publications.required_without' => 'يجب اختيار منشور واحد على الأقل أو مستند واحد على الأقل لإصدار الرخصة.',
            'documents.required_without' => 'يجب اختيار مستند واحد على الأقل أو منشور واحد على الأقل لإصدار الرخصة.',
            'valid_until.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يطابق تاريخ البداية.',

        ];
    }
}

