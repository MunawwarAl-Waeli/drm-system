<?php

namespace Modules\CustomerManagement\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Library\Models\Document;
use Modules\Library\Models\Publication;
use Modules\PublisherWorkspace\Models\Publisher;

class CustomerLicense extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'name',
        'company',
        'publisher_id',
        'type', // individual, group
        'note',
        'max_devices',
        'valid_from',
        'valid_until',
        'never_expires',
        'status',
        'send_via_email'
    ];

    // تحويل التواريخ إلى كائنات Carbon ليسهل التعامل معها
    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'never_expires' => 'boolean',
        'restricted_ips' => 'array',         // إضافة
        'restricted_countries' => 'array',   // إضافة
        'auto_detect_ip' => 'boolean',       // إضافة
        'send_via_email' => 'boolean',
        'auto_detect_country' => 'boolean',  // إضافة
    ];

    /**
     * 📚 علاقة الرخصة بالمنشورات (مع جلب الحقول الوسيطة التي صممناها)
     */
    public function publications()
    {
        return $this->belongsToMany(Publication::class, 'license_publications')
            ->withPivot([
                'access_mode',
                'valid_from',
                'valid_until',
                'status'
            ])
            ->withTimestamps();
    }

    /**
     * 📄 علاقة الرخصة بالملفات الفردية (مع جلب تجاوزات الـ DRM)
     */
    public function documents()
    {
        return $this->belongsToMany(Document::class, 'license_documents')
            ->withPivot([
                'access_mode',
                'valid_from',
                'valid_until',
                'views_override',
                'status'
            ])
            ->withTimestamps();
    }

    /**
     * 💻 علاقة الرخصة بالأجهزة الفردية
     */
    public function devices()
    {
        return $this->belongsToMany(CustomerDevice::class, 'customer_device_license')
            ->withPivot('activated_at', 'status');
    }
     public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'publisher_id');
    }

}
