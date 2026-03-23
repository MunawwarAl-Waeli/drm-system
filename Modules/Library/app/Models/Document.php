<?php

namespace Modules\Library\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\PublisherWorkspace\Models\Publisher;

class Document extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'document_uuid',      // المعرف القادم من C#
        'title',
        'type',
        'description',
        'size',
        'file_hash',
        'original_filename',
        'status',
        'access_scope',
        'publisher_id',
        'publication_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'size' => 'integer',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
    // --- العلاقات ---

    public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'publisher_id');
    }

    public function publication()
    {
        return $this->belongsTo(Publication::class, 'publication_id');
    }

    // علاقة المستند بصندوق المفاتيح (علاقة 1 إلى 1)
    public function key()
    {
        return $this->hasOne(DocumentKey::class, 'document_id');
    }

    // علاقة المستند بقيود الحماية (علاقة 1 إلى 1)
    public function securityControls()
    {
        return $this->hasOne(DocumentSecurityControl::class, 'document_id');
    }

    public function customerlicense()
    {
        return $this->belongsToMany(CustomerLicense::class, 'license_documents');
    }

}
