<?php

namespace Modules\PublisherWorkspace\Models;


use Illuminate\Database\Eloquent\Model;

class PublisherDevice extends Model
{
    protected $fillable = [
        "publisher_id",
        'publisher_license_id',
        'hardware_id',
        'device_name',
        'last_ip',
        'status'
    ];

    // العلاقة مع الرخصة
    public function license()
    {
        return $this->belongsTo(PublisherLicense::class, 'publisher_license_id');
    }
}
