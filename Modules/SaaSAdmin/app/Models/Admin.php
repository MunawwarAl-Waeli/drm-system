<?php

namespace Modules\SaaSAdmin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_loin_at',
        'last_loin_ip',
    ];

    // الإخفاء (لكي لا تظهر كلمة المرور أبداً في الـ API)
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // التحويلات (Cast)
    protected $casts = [
        'password' => 'hashed',
        'is_active' => 'boolean', // ضروري لكي يرجع كـ true/false
        'last_login_at' => 'datetime',
    ];

    // العلاقة: الموظف يمكنه إضافة عدة باقات
    public function packages()
    {
        return $this->hasMany(Package::class, 'admin_id');
    }
}
