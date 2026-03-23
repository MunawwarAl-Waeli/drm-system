<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\SaaSAdmin\Models\Package;


class SeedPackagesCommand extends Command
{
    /**
     * اسم الأمر الذي ستكتبه في التيرمنال لتشغيله
     *
     * @var string
     */
    protected $signature = 'packages:seed';

    /**
     * وصف قصير لما يفعله الأمر
     *
     * @var string
     */
    protected $description = 'إضافة باقات افتراضية إلى قاعدة البيانات';

    /**
     * تنفيذ الأمر
     */
    public function handle()
    {
        $this->info('جاري إنشاء الباقات الافتراضية...');

        // تجهيز مصفوفة بالباقات التي نريد إضافتها
        $packages = [
            [
                'name' => 'الباقة المجانية (تجريبية)',
                'base_price' => 0.00,
                'duration_days' => 14,
                'trial_days' => 14,
                'is_default_registration' => true, // تُمنح تلقائياً لأي مستخدم جديد
                'license_type' => 'Standard',
                'security_grade' => 'Basic',
                'base_max_documents' => 10,
                'base_max_file_size_mb' => 5,
                'base_max_total_storage_mb' => 50,
                'base_batch_size' => 1,
                'base_devices_allowed' => 1,
                'allowed_extensions' => ['pdf', 'docx', 'jpg'],
                'features' => [
                    "can_use_guest_link" => false,
                    "can_use_dynamic_watermark" => false,
                    "allow_custom_splash_screen" => false,
                    "remove_vendor_watermark" => false,
                    "prevent_screen_recording" => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'الباقة القياسية',
                'base_price' => 29.99,
                'duration_days' => 30,
                'trial_days' => 0,
                'is_default_registration' => false,
                'license_type' => 'Standard',
                'security_grade' => 'Standard',
                'base_max_documents' => 100,
                'base_max_file_size_mb' => 25,
                'base_max_total_storage_mb' => 1024, // 1 GB
                'base_batch_size' => 5,
                'base_devices_allowed' => 3,
                'allowed_extensions' => ['pdf'],
                'features' => [
                    "can_use_guest_link" => false,
                    "can_use_dynamic_watermark" => false,
                    "allow_custom_splash_screen" => false,
                    "remove_vendor_watermark" => false,
                    "prevent_screen_recording" => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'باقة الشركات (Enterprise)',
                'base_price' => 199.99,
                'duration_days' => 365,
                'trial_days' => 0,
                'is_default_registration' => false,
                'license_type' => 'Enterprise',
                'security_grade' => 'Ultra',
                'base_max_documents' => 10000,
                'base_max_file_size_mb' => 100,
                'base_max_total_storage_mb' => 51200, // 50 GB
                'base_batch_size' => 50,
                'base_devices_allowed' => 10,
                'allowed_extensions' => ['*'], // كل الصيغ
                'features' => [
                    "can_use_guest_link" => true,
                    "can_use_dynamic_watermark" => false,
                    "allow_custom_splash_screen" => true,
                    "remove_vendor_watermark" => false,
                    "prevent_screen_recording" => true,
                ],
            ]
        ];

        // استخدام updateOrCreate أفضل من create لكي لا تتكرر البيانات إذا قمت بتشغيل الأمر أكثر من مرة
        foreach ($packages as $pkgData) {
            Package::updateOrCreate(
                ['name' => $pkgData['name']], // الشرط الذي يبحث به (اسم الباقة)
                $pkgData // البيانات التي سيتم إضافتها أو تحديثها
            );
        }

        $this->info('تم إنشاء الباقات بنجاح!');

    }
}
