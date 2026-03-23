<?php
namespace Modules\ReaderApp\Transformers\Verification;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ReaderApp\Transformers\Traits\ExtractsFlatRulesTrait; // 👈 الاستدعاء

class DocumentVerificationResource extends JsonResource
{
    use ExtractsFlatRulesTrait; // 👈 التفعيل

    public function toArray($request)
    {
        // 👈 استخدامها وتمرير الملف والـ pivot الذي ألصقناه في السيرفس
        return $this->extractFlatRules($this->resource, $this->pivot_overrides);
    }
}
