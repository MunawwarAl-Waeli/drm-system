<?php
namespace Modules\Library\App\Transformers;


use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class ProtectedDocumentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            // العنوان (Title) مثل citrix_guidelines
            'title' => $this->title,

            // التفاصيل الداخلية
            'id' => $this->id,
            'published' => Carbon::parse($this->published_at)->format('m-d-Y H:i:s'),
            'status' => $this->status, // valid, suspended, expired

            // إضافة برمجية لتطابق واجهة LockLizard
            'web_viewer_access' => 'no',
        ];
    }
}
?>