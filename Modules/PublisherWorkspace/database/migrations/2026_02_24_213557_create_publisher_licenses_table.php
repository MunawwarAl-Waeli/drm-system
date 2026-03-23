<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {

        Schema::create('publisher_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('publishers')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->restrictOnDelete();

            // --- [جديد] بيانات الرخصة الفعلية ---
            $table->string('license_key')->unique(); // المفتاح الذي سيدخله الناشر في برنامج Writer (مثال: XXXX-XXXX-XXXX)
            $table->text('public_certificate')->nullable();
            $table->text('lic_file_path')->nullable(); // [جديد] مسار حفظ ملف lzpk لكي يتمكن الناشر من إعادة تحميله من لوحته


            $table->unsignedInteger('max_documents')->nullable();
            $table->unsignedBigInteger('max_total_storage_mb')->nullable();
            $table->unsignedBigInteger('max_file_size_mb')->default(0);
            $table->unsignedInteger('devices_allowed')->nullable();
            $table->unsignedInteger('batch_size')->nullable();
            // --- حالة الاشتراك والمالية ---
            $table->enum('status', ['trial', 'active', 'suspended', 'expired'])->default('trial');
            $table->boolean('is_paid')->default(false); // [جديد] هل تم دفع ثمن هذه الرخصة فعلياً؟

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('publisher_licenses');
    }
};









