<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('license_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_license_id')->constrained('customer_licenses')->cascadeOnDelete();

            // نربطه بجدول documents الموجود في وحدة المكتبة
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();

            $table->enum('access_mode', ['unlimited', 'limited','baselimited'])->default('baselimited');

            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();

            // 2. تجاوزات قيود الـ DRM (Print & View)
            // null = اترك الإعداد الأصلي للملف | 0 = ممنوع | رقم = عدد المرات المسموحة
            $table->integer('prints_override')->nullable();
            $table->integer('views_override')->nullable();
            $table->enum('status', ['active', 'suspended', 'revoked'])->default('active');

            $table->timestamps();

            // منع تكرار نفس الملف لنفس الرخصة
            $table->unique(['customer_license_id', 'document_id'], 'license_doc_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('license_documents');
    }
};
