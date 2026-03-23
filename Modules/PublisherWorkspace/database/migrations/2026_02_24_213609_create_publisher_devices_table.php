<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('publisher_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('publishers')->cascadeOnDelete();
            $table->foreignId('publisher_license_id')->constrained('publisher_licenses')->cascadeOnDelete();

            $table->string('hardware_id');
            $table->string('device_name')->nullable();

            // --- [جديد] التتبع السحابي (Telemetry) ---
            $table->string('os_version')->nullable(); // نظام التشغيل (مثال: Windows 11)
            $table->string('app_version')->nullable(); // إصدار برنامج Writer الذي يستخدمه
            $table->string('last_ip')->nullable();
            $table->timestamp('last_synced_at')->nullable(); // متى آخر مرة اتصل هذا الجهاز بسيرفرنا؟ (مهم جداً لاكتشاف التلاعب)

            $table->enum('status', ['active', 'revoked'])->default('active');

            $table->unique(['publisher_license_id', 'hardware_id']);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('publisher_devices');
    }
};
