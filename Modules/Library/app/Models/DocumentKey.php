<?php

namespace Modules\Library\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentKey extends Model
{
    protected $fillable = [
        'document_id',
        'encrypted_key',
        'key_version',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'key_version' => 'integer',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
