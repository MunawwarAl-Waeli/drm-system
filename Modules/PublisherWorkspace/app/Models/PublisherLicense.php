<?php

namespace Modules\PublisherWorkspace\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Modules\SaaSAdmin\Models\Package;

class PublisherLicense extends Model
{
    protected $guarded = [];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_paid' => 'boolean',
    ];

    // --- العلاقات ---

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    // --- منطق الوراثة (Accessors) ---
    // هذا المنطق يضمن جلب القيمة من الباقة إذا كان الحقل في الرخصة فارغاً (NULL)

    protected function actualMaxDocuments(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->max_documents ?? $this->package->base_max_documents,
        );
    }

    protected function actualMaxTotalStorageMb(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->max_total_storage_mb ?? $this->package->base_max_total_storage_mb,
        );
    }

    protected function actualDevicesAllowed(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->devices_allowed ?? $this->package->base_devices_allowed,
        );
    }

    protected function actualBatchSize(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->batch_size ?? $this->package->base_batch_size,
        );
    }
      protected function actualMaxFileSizeMb(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->max_file_size_Mb ?? $this->package->base_max_file_size_Mb,
        );
    }

}
