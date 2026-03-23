<?php

namespace Modules\Library\Models;

// use Filament\Forms\Components\RichEditor\Actions\CustomBlockAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\PublisherWorkspace\Models\Publisher;

class Publication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'obey_customer_start_date',
        'publisher_id',
    ];

    protected $casts = [
        'obey_customer_start_date' => 'boolean',
    ];

    // علاقة المنشور بالناشر (من موديول الساس)
    public function publisher()
    {
        return $this->belongsTo(Publisher::class, 'publisher_id');
    }

    // علاقة المنشور بالملفات التي بداخله
    public function documents()
    {
        return $this->hasMany(Document::class, 'publication_id');
    }

    public function customerlicense()
    {
        return $this->belongsToMany(CustomerLicense::class, 'license_publications')
            ->withPivot([
                'access_mode',
                'valid_from',
                'valid_until',
                'status'
            ])
            ->withTimestamps();
    }

}
