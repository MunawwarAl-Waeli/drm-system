<?php

namespace Modules\CustomerManagement\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\CustomerManagement\Models\CustomerLicense;
use Modules\CustomerManagement\Models\Voucher;

class LicenseService
{
    /**
     * 👤 إنشاء رخصة فردية
     */
    public function createIndividualLicense(int $publisherId, array $data)
    {
        return DB::transaction(function () use ($publisherId, $data) {

            // 2. إنشاء الرخصة الفردية
            $license = CustomerLicense::create([
                'publisher_id' => $publisherId,
                'name' => $data['name'],
                'company' => $data['company']??null,
                'email' => $data['email'],
                'type' => 'individual',
                'note' => $data['note'] ?? null,
                'max_devices' => $data['max_devices'] ?? 1,
                'valid_from' => $data['valid_from'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'never_expires' => $data['never_expires'] ?? false,
                'send_via_email' => $data['send_via_email'] ?? false,

            ]);

            // 3. ربط الملفات والمنشورات
            if (!empty($data['publications']))
                $license->publications()->sync($data['publications']);
            if (!empty($data['documents']))
                $license->documents()->sync($data['documents']);


            // 1. تحميل العلاقات مع تحديد الأعمدة المطلوبة فقط
            $license->load('publications:id,name', 'documents:id,title');

            // 2. إخفاء بيانات الجدول الوسيط (pivot) لجعل الرد أنظف وأصغر
            $license->publications->makeHidden('pivot');
            $license->documents->makeHidden('pivot');

            return $license;
        });
    }
    // if (!empty($data['publications'])) {
//     // تمرر مصفوفة الأرقام العادية أولاً، ثم مصفوفة البيانات الإضافية ثانياً
//     $license->publications()->syncWithPivotValues(
//         $data['publications'],
//         ['status' => 'active', 'assigned_at' => now()] // هذه البيانات ستضاف لكل المنشورات
//     );
// }
    /**
     * 🎟️ إنشاء رخصة جماعية وتوليد الكروت
     */
    public function createGroupVouchers(int $publisherId, array $data)
    {
        return DB::transaction(function () use ($publisherId, $data) {
            // 1. إنشاء أو جلب العميل (الجهة المشترية/المكتبة)

            // 2. إنشاء الرخصة الأم (Master License)
            $masterLicense = CustomerLicense::create([
                'publisher_id' => $publisherId,
                'name' => $data['name'],
                'company' => $data['company'] ?? null,
                'email' => $data['email'],
                'type' => 'individual',
                'note' => $data['note'] ?? null,
                'max_devices' => $data['max_devices'] ?? 1,
                'valid_from' => $data['valid_from'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'never_expires' => $data['never_expires'] ?? false,
                'send_via_email' => $data['send_via_email'] ?? false,

            ]);

            // ربط الصلاحيات بالرخصة الأم
            if (!empty($data['publications']))
                $masterLicense->publications()->sync($data['publications']);
            if (!empty($data['documents']))
                $masterLicense->documents()->sync($data['documents']);

            // 3. 🚀 توليد الكروت (Bulk Insert من أجل الأداء)
            $vouchersData = [];
            $now = now();
            for ($i = 0; $i < $data['vouchers_count']; $i++) {
                $vouchersData[] = [
                    'customer_license_id' => $masterLicense->id,
                    // 'publisher_id' => $publisherId, // الـ Denormalization الذي اتفقنا عليه لتسريع البحث
                    'pin_code' => strtoupper(Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4) . '-' . Str::random(4)),
                    'status' => 'active',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // إدخال الكروت دفعة واحدة لقاعدة البيانات (سريع جداً حتى لو كانت 5000 كرت)
            Voucher::insert($vouchersData);

            return $masterLicense; // يمكننا لاحقاً إرجاع الكروت لتصديرها كـ CSV
        });
    }
}
