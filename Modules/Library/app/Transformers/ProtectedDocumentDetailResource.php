<?php
    namespace Modules\Library\App\Transformers;


use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ProtectedDocumentDetailResource extends JsonResource
{
    public function toArray($request)
    {
        // جلب علاقة إعدادات الأمان (يجب أن تكون معرفة في موديل Document كـ hasOne)
        $controls = $this->securityControls;

        // 1. معالجة نص الانتهاء (Expires)
        $expiresText = 'never';
        if ($controls) {
            if ($controls->expiry_mode === 'fixed_date') {
                $expiresText = Carbon::parse($controls->expiry_date)->format('m-d-Y');
            } elseif ($controls->expiry_mode === 'days_from_first_use') {
                $expiresText = $controls->expiry_days . ' days from first use';
            }
        }

        // 2. معالجة نطاق الوصول (Access)
        $accessText = match ($this->access_scope) {
            'all_customers' => 'All customers',
            'selected_customers' => 'Selected customers',
            'publication' => 'Publication only',
            default => 'Unknown',
        };

        // 3. معالجة إعدادات التحقق (Validity check)
        $validityCheck = 'never';
        if ($controls) {
            $validityCheck = match ($controls->verify_mode) {
                'each_time' => 'Each time the document is opened',
                'only_when_internet' => 'Only when internet is available',
                'every_x_days' => 'Every ' . $controls->verify_frequency_days . ' days',
                'after_x_days_then_never' => 'After ' . $controls->verify_frequency_days . ' days, then never',
                default => 'never',
            };
        }

        return [
            // البيانات الأساسية العلوية
            'title' => $this->title,
            'id' => $this->id,
            'published' => Carbon::parse($this->published_at)->format('m-d-Y H:i:s'),
            'expires' => $expiresText,
            'status' => $this->status,

            // نطاق الوصول
            'access' => $accessText,

            // ضوابط المستند (Document Controls) - مقتصرة على جداولنا فقط
            'document_controls' => [
                'validity_check' => $validityCheck,
                'view_limitation' => ($controls && $controls->max_views_allowed) ? $controls->max_views_allowed . ' views allowed' : 'unlimited',
                // أضفنا الوصف هنا كونه يمكن أن يمثل معلومات إضافية للمستند
                'description' => $this->description,
            ],

            // حقل الملاحظات (Document Notes) الذي سيتم تعديله في الأسفل
            // سنستخدم حقل description أو يمكننا إضافة حقل notes لاحقاً، لكن حالياً description يفي بالغرض
            'notes' => $this->description,
        ];
    }
}
?>
