<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            // المعرف الفريد القادم من برنامج الـ C# (مهم جداً لربط الملف بالرخصة لاحقاً)
            $table->uuid('document_uuid')->unique();

            // البيانات الوصفية للملف (Metadata)
            $table->string('title');
            $table->enum('type', ['pdf', 'video', 'audio', 'other'])->default('pdf');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('size')->nullable(); // حجم الملف بالبايت

            // بصمة الملف (Hash) لضمان أن العميل يفتح الملف الأصلي ولم يدمج معه فايروس
            $table->string('file_hash',64)->nullable();
            $table->string('original_filename')->nullable();

            // حالات المستند المطابقة لنظام LockLizard
            $table->enum('status', ['valid', 'suspended', 'expired'])->default('valid');
            $table->timestamp('published_at')->useCurrent();

            // نطاق الوصول
            $table->enum('access_scope', ['all_customers', 'selected_customers', 'publication'])->default('selected_customers');

            // العلاقات
            $table->foreignId('publisher_id')->constrained('publishers')->cascadeOnDelete();
            $table->foreignId('publication_id')->nullable()->constrained('publications')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
