<?php

namespace Modules\PublisherWorkspace\Models;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Modules\SaaSAdmin\Models\Admin;
// class Publisher extends Model
// {
//     use SoftDeletes, Notifiable;

//     protected $fillable = [
//         'name',
//         'email',
//         'company',
//         'ecommerce_key',
//         'status',
//         'created_by'
//     ];

//     // العلاقة: من هو الموظف الذي أضاف هذا الناشر؟
//     public function creator()
//     {
//         return $this->belongsTo(Admin::class, 'created_by');
//     }

//     // العلاقة: رخصة الناشر (يمتلك رخصة واحدة نشطة عادة)
//     public function license()
//     {
//         return $this->hasOne(PublisherLicense::class, 'publisher_id');
//     }
// }


class Publisher extends Authenticatable
{
    use Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'company',
        'country',
        'vat_number',
        'ecommerce_key',
        'status',
        'created_by'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
    ];

    // علاقة: من هو الموظف الذي أنشأ هذا الناشر؟
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
    public function licenses()
    {
        return $this->hasMany(PublisherLicense::class);
    }
}
