<?php

namespace Modules\SaaSAdmin\Models;

use Illuminate\Database\Eloquent\Model;
class Package extends Model
{
    // السماح بإدخال هذه الحقول
    protected $fillable = [
        'name',
        'base_price',
        'duration_days',
        'trial_days',
        'is_default_registration',
        'license_type',
        'security_grade',
        'base_max_documents',
        'base_max_file_size_mb',
        'base_max_total_storage_mb',
        'base_batch_size',
        'base_devices_allowed',
        'allowed_extensions',
        'features',
        'is_active'
    ];

    // السحر هنا: إخبار النظام أن هذه الأعمدة عبارة عن مصفوفات (JSON) وليست نصوصاً عادية
    protected $casts = [
        'features' => 'array',
        'allowed_extensions' => 'array',
        'is_active' => 'boolean',
        'is_default_registration' => 'boolean',
    ];
}
