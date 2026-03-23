<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            // ترتبط بالرخصة الجماعية الأب
            $table->foreignId('customer_license_id')->constrained('customer_licenses')->cascadeOnDelete();

            $table->string('pin_code')->unique(); // الرقم السري للكرت
            $table->enum('status', ['available', 'active', 'revoked'])->default('available');

            // الطالب الذي استخدم الكرت (يمتلئ عند التفعيل)
            $table->foreignId('used_by_customer_id')->nullable()->constrained('readers')->nullOnDelete();

            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vouchers');
    }
};
