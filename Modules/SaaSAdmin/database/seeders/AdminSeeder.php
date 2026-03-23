<?php

namespace Modules\SaaSAdmin\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\SaaSAdmin\Models\Admin;
// use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // التحقق أولاً كي لا يتم تكرار الحساب إذا قمت بتشغيل الأمر مرتين
        $admin = Admin::where('email', 'admin@saas.com')->first();

        if (!$admin) {
            Admin::create([
                'name' => 'المدير العام',
                'email' => 'admin@saas.com',
                'password' => 'password123', // سيتم تشفيرها تلقائياً بفضل الـ cast الذي أضفناه في الموديل
                'role' => 'super_admin',
                'is_active' => true,
            ]);

            $this->command->info('تم إنشاء حساب المدير بنجاح!');
        } else {
            $this->command->info('حساب المدير موجود مسبقاً.');
        }
    }
}
