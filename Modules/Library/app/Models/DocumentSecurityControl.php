<?php

namespace Modules\Library\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentSecurityControl extends Model
{
    protected $fillable = [
        'document_id',
        'expiry_mode',
        'expiry_date',
        'expiry_days',
        'verify_mode',
        'verify_frequency_days',
        'grace_period_days',
        'max_views_allowed',
    ];

    protected $casts = [
        // تحويلات التواريخ
        'expiry_date' => 'datetime',

        // تحويلات الأرقام
        'expiry_days' => 'integer',
        'verify_frequency_days' => 'integer',
        'grace_period_days' => 'integer',
        'max_views_allowed' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
