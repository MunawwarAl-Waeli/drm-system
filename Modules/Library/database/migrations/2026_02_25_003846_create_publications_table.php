<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('publications', function (Blueprint $table) {
            $table->id();

            // اسم المنشور (حددنا الطول بـ 64 لتجنب مشاكل مسارات المجلدات في أنظمة التشغيل)
            $table->string('name', 64);
            $table->text('description')->nullable();

            // قيد تاريخ البدء (لحجب الأعداد القديمة من المجلة عمن اشترك حديثاً)
            $table->boolean('obey')->default(false);
            $table->enum('status', ['active', 'suspend'])->default('active');
            // ربط المنشور بالناشر (هذا الجدول موجود في موديول SaaSAdmin)
            $table->foreignId('publisher_id')->constrained('publishers')->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('publications');
    }
};
