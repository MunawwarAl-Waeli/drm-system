<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('readers', function (Blueprint $table) {
            $table->id();
            // البيانات الأساسية التي ستُطبع لاحقاً كعلامة مائية
            $table->string('name');
            // الإيميل فريد (Unique) على مستوى النظام بالكامل
            $table->string('email')->unique();

            // للتحقق من أن الإيميل حقيقي عبر الـ OTP
            $table->timestamp('email_verified_at')->nullable();

            // الباسوورد الخاص بتسجيل الدخول لبرنامج القارئ
            $table->string('password');

            // لتسجيل الخروج من كل الأجهزة وغيرها من ميزات الأمان
            $table->rememberToken();

            // حقل إضافي للأمان: حظر حساب قارئ بالكامل إذا ثبت أنه يسرب الملفات
            $table->boolean('is_banned')->default(false);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('readers');
    }
};
