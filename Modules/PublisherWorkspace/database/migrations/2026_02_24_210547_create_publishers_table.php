<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('publishers', function (Blueprint $table) {
            $table->id();

            // بيانات الحساب والدخول
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password'); // ضروري لكي يسجل الناشر دخوله
            $table->string('phone')->nullable(); // للتواصل والدعم

            // بيانات الشركة والفوترة (ضروري جداً لـ SaaS)
            $table->string('company')->nullable();
            $table->string('country')->nullable(); // للإحصائيات والضرائب
            $table->string('vat_number')->nullable(); // الرقم الضريبي

            // بيانات النظام والربط
            $table->string('ecommerce_key')->unique()->nullable();
            $table->enum('status', ['active', 'suspended', 'banned'])->default('active'); // أضفنا banned للحظر النهائي

            // [الجديد] التتبع ومصدر التسجيل
            $table->enum('registration_source', ['website', 'admin_panel', 'api'])->default('website'); // لمعرفة من أين جاء العميل
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete(); // إذا أنشأه موظف الإدارة

            // بيانات لارافيل الافتراضية والأمان
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    public function down()
    {
        Schema::dropIfExists('publishers');
    }
};
