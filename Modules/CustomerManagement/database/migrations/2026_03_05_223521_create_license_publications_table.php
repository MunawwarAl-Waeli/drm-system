<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('license_publications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_license_id')->constrained('customer_licenses')->cascadeOnDelete();

            // نربطه بجدول publications الموجود في وحدة المكتبة (Library)
            $table->foreignId('publication_id')->constrained('publications')->cascadeOnDelete();

            // تواريخ الوصول (Unlimited vs Limited)
            $table->enum('access_mode', [ 'unlimited', 'limited'])->default('unlimited');
            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();

            // 🚨 حالة وصول هذا العميل المحددة للمنشور (تعليق/إلغاء للطالب فقط)
            $table->enum('status', ['active', 'suspended', 'revoked'])->default('active');

            $table->timestamps();

            // منع تكرار نفس المنشور لنفس الرخصة
            $table->unique(['customer_license_id', 'publication_id'], 'license_pub_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('license_publications');
    }
};
