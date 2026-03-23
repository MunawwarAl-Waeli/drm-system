<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('customer_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reader_id')->nullable()->constrained('readers')->nullOnDelete();

            $table->string('hardware_id')->unique(); // بصمة الجهاز الفريدة
            $table->string('device_name')->nullable();
            $table->string('device_type')->nullable();
            $table->string('os_version')->nullable();
            $table->string('app_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamp('last_synced_at')->nullable();

            $table->enum('status', ['active', 'blocked'])->default('active');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_devices');
    }
};
