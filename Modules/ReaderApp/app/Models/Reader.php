<?php

namespace Modules\ReaderApp\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Modules\ReaderApp\Database\Factories\ReaderFactory;
use Laravel\Sanctum\HasApiTokens;
class Reader extends Model
{
    use HasFactory, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ["name","password","email"];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'password' => 'hashed',
    ];

    // protected static function newFactory(): ReaderFactory
    // {
    //     // return ReaderFactory::new();
    // }
}
