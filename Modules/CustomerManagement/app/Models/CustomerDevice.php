<?php

namespace Modules\CustomerManagement\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CustomerDevice extends Model
{
    use HasFactory;


    protected $fillable = [
        'hardware_id',
        'reader_id',      // 👈 هذا هو الجاني الذي كان مفقوداً!
        'status',
        'device_name',
        'device_type',
        'os_version',
        'app_version',
        'ip_address',
        'last_synced_at',
    ];


    /**
     * 🔑 علاقة الجهاز بالرخص الفردية
     * يجلب الرخص التي تم تفعيلها على هذا الجهاز
     */
    public function licenses()
    {
        return $this->belongsToMany(CustomerLicense::class, 'customer_device_license')
            ->withPivot('activated_at') // جلب تاريخ التفعيل من الجدول الوسيط
            ->withTimestamps();
    }

    /**
     * 🎟️ علاقة الجهاز بالكروت (القسائم الجماعية)
     * يجلب الكروت التي تم تفعيلها على هذا الجهاز
     */
    public function vouchers()
    {
        return $this->belongsToMany(Voucher::class, 'customer_device_voucher')
            ->withPivot('activated_at')
            ->withTimestamps();
    }


    public function hasAccessToLicense(int $licenseId): bool
    {
        $licenseLink = DB::table('customer_device_license')
            ->where('customer_device_id', $this->id)
            ->where('customer_license_id', $licenseId)
            ->where('status', 'active')
            ->exists();

        if ($licenseLink)
            return true;

        $voucherLink = DB::table('customer_device_voucher')
            ->join('vouchers', 'customer_device_voucher.voucher_id', '=', 'vouchers.id')
            ->where('customer_device_voucher.customer_device_id', $this->id)
            ->where('customer_device_voucher.status', 'active')
            ->where('vouchers.customer_license_id', $licenseId)
            ->exists();

        return $voucherLink;
    }
}
