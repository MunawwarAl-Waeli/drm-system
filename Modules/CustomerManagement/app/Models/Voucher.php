<?php

namespace Modules\CustomerManagement\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_license_id', // الرخصة الأم (التي تحدد ما هي الملفات المسموحة وتاريخ الانتهاء)
        'code',                // كود التفعيل (مثل: X4F9-P2M1-88DC-V9QW)
        'status',              // active, used, suspended (حالة الكرت نفسه)
    ];

    /**
     * الكرت ينتمي لرخصة أم (Master License) واحدة
     * منها يرث الكرت صلاحيات فتح الملفات والمنشورات
     */
    public function license()
    {
        return $this->belongsTo(CustomerLicense::class, 'customer_license_id');
    }

    /**
     * 💻 علاقة الكرت بالأجهزة
     * الكرت يمكن تفعيله على جهاز أو أكثر (حسب ما يحدده حقل max_devices في الرخصة الأم)
     */
    public function devices()
    {
        return $this->belongsToMany(CustomerDevice::class, 'customer_device_voucher')
            ->withPivot('activated_at')
            ->withTimestamps();
    }
}
