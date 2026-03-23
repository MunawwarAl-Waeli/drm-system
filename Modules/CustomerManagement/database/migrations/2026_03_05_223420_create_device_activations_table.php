<?php



use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        // الجدول الوسيط للرخص الفردية والأجهزة
        Schema::create('customer_device_license', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_license_id')->constrained('customer_licenses')->cascadeOnDelete();
            $table->foreignId('customer_device_id')->constrained('customer_devices')->cascadeOnDelete();
            $table->enum('status', ['active', 'bloked'])->default('active');
            $table->timestamp('activated_at')->useCurrent();

            // منع تكرار نفس الجهاز لنفس الرخصة
            $table->unique(['customer_license_id', 'customer_device_id'], 'license_device_unique');
        });

        // الجدول الوسيط للكروت والأجهزة
        Schema::create('customer_device_voucher', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers')->cascadeOnDelete();
            $table->foreignId('customer_device_id')->constrained('customer_devices')->cascadeOnDelete();
            $table->timestamp('activated_at')->useCurrent();
            $table->enum('status', ['active', 'bloked'])->default('active');
            // منع تكرار نفس الجهاز لنفس الكرت
            $table->unique(['voucher_id', 'customer_device_id'], 'voucher_device_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_device_voucher');
        Schema::dropIfExists('customer_device_license');
    }
};
