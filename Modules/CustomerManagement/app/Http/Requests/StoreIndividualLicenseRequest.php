<?php

namespace Modules\CustomerManagement\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Validation\Rule;

class StoreIndividualLicenseRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $publisherId = $this->user()->id;
        return [
            'name' => 'required|string|max:255', // اسم الطالب
            'email' => 'required|email|max:255', // إيميل الطالب
            'company' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:255',
            'max_devices' => 'required|integer|min:1',
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

    // 🌟 رسائل مخصصة للأخطاء
    public function messages()
    {
        return [
            'publications.required_without' => 'يجب اختيار منشور واحد على الأقل أو مستند واحد على الأقل لإصدار الرخصة.',
            'documents.required_without' => 'يجب اختيار مستند واحد على الأقل أو منشور واحد على الأقل لإصدار الرخصة.',
            'publications.*.distinct' => 'لقد قمت باختيار نفس المنشور أكثر من مرة، يرجى إزالة التكرار.',
            'documents.*.distinct' => 'لقد قمت باختيار نفس المستند أكثر من مرة، يرجى إزالة التكرار.',
            'valid_until.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يطابق تاريخ البداية.',
            'type.in' => 'نوع الرخصة يجب أن يكون إما فردية (individual) أو جماعية (group).',
        ];
    }
}
