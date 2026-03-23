<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('document_security_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();

            // --- 1. إعدادات انتهاء الصلاحية (Expiry) ---
            $table->enum('expiry_mode', ['never', 'fixed_date', 'days_from_first_use'])->default('never');
            $table->dateTime('expiry_date')->nullable();
            $table->unsignedInteger('expiry_days')->nullable(); // عدد الأيام من أول فتح للملف

            // --- 2. إعدادات التحقق والاتصال بالإنترنت (Offline Mode) ---
            $table->enum('verify_mode', [
                'never',
                'each_time',
                'only_when_internet',
                'every_x_days',
                'after_x_days_then_never'
            ])->default('never');
            $table->unsignedInteger('verify_frequency_days')->nullable();
            $table->unsignedInteger('grace_period_days')->nullable();

            // --- 3. إعدادات المشاهدة (Viewing) - إضافة جديدة للمستقبل ---
            // هل ينتهي الملف بعد فتحه 5 مرات مثلاً؟
            $table->unsignedInteger('max_views_allowed')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_security_controls');
    }
};
