<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // Schema::create('admins', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('name'); // اسم الموظف
        //     $table->string('email')->unique(); // البريد الإلكتروني (يجب أن يكون فريداً)
        //     $table->string('password'); // كلمة المرور المشفرة

        //     // الصلاحيات (super_admin له كل الصلاحيات، البقية مقيدون)
        //     $table->enum('role', ['super_admin', 'support', 'billing'])->default('support');

        //     // حالة الحساب (لإيقاف موظف استقال دون حذف سجلاته)
        //     $table->boolean('is_active')->default(true);

        //     $table->timestamps();
        // });


        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar')->nullable(); // صورة الموظف (لجمالية Filament)

            $table->enum('role', ['super_admin', 'support', 'billing'])->default('support');
            $table->boolean('is_active')->default(true);

            $table->timestamp('last_login_at')->nullable(); // [جديد] لمعرفة هل الموظف نشط أم لا
            $table->string('last_login_ip')->nullable(); // [جديد] للأمان، من أين دخل؟

            $table->rememberToken();
            $table->timestamps();
        });

    }

    public function down()
    {
        Schema::dropIfExists('admins');
    }
};
