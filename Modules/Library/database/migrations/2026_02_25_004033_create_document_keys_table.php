<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('document_keys', function (Blueprint $table) {
            $table->id();

            // الربط الحصري بالملف (لو انحذف الملف، ينحذف مفتاحه)
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();

            // المفتاح المشفر (يأتينا من الـ C# مشفراً بمفتاح السيرفر العام)
            $table->text('encrypted_key');

            // إصدار المفتاح (للمستقبل: لو الناشر أعاد تشفير الملف بمفتاح جديد)
            $table->unsignedInteger('key_version')->default(1);

            // زر طوارئ لإبطال مفتاح معين لو تم تسريبه
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_keys');
    }
};
