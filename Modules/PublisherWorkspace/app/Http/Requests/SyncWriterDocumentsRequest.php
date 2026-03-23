<?php

namespace Modules\PublisherWorkspace\Http\Requests;



class SyncWriterDocumentsRequest extends BaseReaderRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // 2. ربط المنشور (اختياري، لأن الناشر قد يرفع ملفات حرة لا تتبع لمنشور)
            'publication_id' => 'nullable|exists:publications,id',

            // 3. مصفوفة الملفات (يجب أن يرسل الـ C# مصفوفة باسم documents)
            'documents' => 'required|array|min:1',

            // --- التحقق من كل ملف داخل المصفوفة (بناءً على الجداول التي صممناها) ---
            'documents.*.document_uuid' => 'required|uuid|unique:documents,document_uuid',
            'documents.*.title' => 'required|string|max:255',
            'documents.*.type' => 'required|integer|in:1,2',
            'documents.*.size' => 'required|integer',
            'documents.*.file_hash' => 'required|string',

            // مفتاح الملف المشفر (مهم جداً!)
            'documents.*.encrypted_key' => 'required|string',

            // --- التحقق من مصفوفة القيود الأمنية الخاصة بكل ملف ---
            'documents.*.security_controls' => 'required|array',
            'documents.*.security_controls.expiry_mode' => 'required|integer|in:1,2,3',
            'documents.*.security_controls.expiry_date' => 'nullable|date',
            'documents.*.security_controls.expiry_days' => 'nullable|integer',
            'documents.*.security_controls.verify_mode' => 'required|integer|in:1,2.3,4,5',
            'documents.*.security_controls.verify_frequency_days' => 'nullable|integer',
            'documents.*.security_controls.grace_period_days' => 'nullable|integer',

        ];
    }

    public function messages()
    {
        return [
            'documents.required' => 'يجب إرسال ملف واحد على الأقل.',
            'documents.*.document_uuid.unique' => 'أحد الملفات المرفوعة موجود مسبقاً في النظام.',
        ];
    }
}
