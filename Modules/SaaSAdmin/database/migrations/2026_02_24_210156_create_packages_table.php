<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {

        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('base_price', 10, 2)->default(0);

            // --- [جديد] دورة الفوترة والتجربة ---
            $table->unsignedInteger('duration_days')->default(30); // مدة الباقة (30 يوم، 365 يوم)
            $table->unsignedInteger('trial_days')->default(0); // هل لها فترة تجريبية مجانية؟ (مثلا 14)
            $table->boolean('is_default_registration')->default(false); // [سحر الأتمتة] لو true، تُمنح تلقائياً لأي شخص يسجل جديد

            // التصنيفات
            $table->enum('license_type', ['Standard', 'Enterprise', 'Educational'])->default('Standard');
            $table->enum('security_grade', ['Basic', 'Standard', 'High', 'Ultra'])->default('Standard');

            // الحصص (Quotas)
            $table->unsignedInteger('base_max_documents')->default(0);
            $table->unsignedBigInteger('base_max_file_size_mb')->default(0);
            $table->unsignedBigInteger('base_max_total_storage_mb')->default(0);
            $table->unsignedInteger('base_batch_size')->default(1);
            $table->unsignedInteger('base_devices_allowed')->default(1);

            // السياسات والميزات (يفضل جعلها JSON لسهولة التوسع بـ Filament)
            $table->json('allowed_extensions')->nullable();
            $table->json('features')->nullable(); // [جديد] تخزن الميزات مثل {"guest_link": true, "watermark": false}

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('packages');
    }
};


// features
// {
//   "can_use_guest_link": true,
//   "can_use_dynamic_watermark": false,
//   "allow_custom_splash_screen": true,
//   "remove_vendor_watermark": false,
//   "prevent_screen_recording": true
// }
