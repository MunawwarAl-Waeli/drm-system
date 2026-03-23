<?php

namespace Modules\CustomerManagement\Actions;

use Illuminate\Support\Collection;
use Modules\CustomerManagement\Models\CustomerLicense;

class ExportVouchersCsvAction
{
    /**
     * تحويل الكروت إلى ملف CSV وتجهيزه للتحميل
     */
    public function execute(CustomerLicense $masterLicense, Collection $vouchers): string
    {
        // فتح ملف وهمي في الذاكرة العشوائية للسيرفر (سريع جداً ولا يستهلك مساحة التخزين)
        $stream = fopen('php://memory', 'w+');

        // إضافة (BOM) لضمان دعم اللغة العربية في برنامج Excel
        fputs($stream, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // كتابة العناوين الرئيسية للأعمدة (Headers)
        fputcsv($stream, [
            'رقم الكرت (Voucher Code)',
            'رقم الرخصة الأم',
            'الأجهزة المسموحة',
            'تاريخ الانتهاء'
        ]);

        // المرور على الكروت وكتابتها في الملف
        foreach ($vouchers as $voucher) {
            fputcsv($stream, [
                $voucher->pin_code,
                $masterLicense->id,
                $masterLicense->max_devices,
                $masterLicense->valid_until ? $masterLicense->valid_until->format('Y-m-d') : 'مدى الحياة',
            ]);
        }

        // العودة لبداية الملف لقراءته
        rewind($stream);

        // استخراج المحتوى كنص
        $csvData = stream_get_contents($stream);

        // إغلاق الملف الوهمي
        fclose($stream);

        return $csvData;
    }
}
