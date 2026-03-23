<?php
namespace Modules\Library\App\Services;

use Illuminate\Support\Facades\DB;
use Modules\Library\Models\Publication;

class PublicationService
{
    /**
     * تنفيذ الإجراءات الجماعية على المنشورات
     */
    public function handleBatchAction(array $publicationIds, string $action, int $publisherId)
    {
        // التأكد من أن المنشورات تابعة للناشر الحالي فقط كإجراء أمني
        $query = Publication::whereIn('id', $publicationIds)
            ->where('publisher_id', $publisherId);

        DB::transaction(function () use ($query, $action) {
            switch ($action) {
                case 'delete':
                    $query->delete(); // سيقوم بالـ Soft Delete لأنك أضفته في الـ Migration
                    break;
                case 'enable_obey':
                    $query->update(['obey' => true]);
                    break;
                case 'disable_obey':
                    $query->update(['obey' => false]);
                    break;
            }
        });
    }
}
?>
