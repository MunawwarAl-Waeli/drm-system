<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('customer_licenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('publisher_id')->constrained('publishers')->cascadeOnDelete();
            $table->foreignId('reader_id')->nullable()->constrained('readers')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('company')->nullable();

            $table->string('note')->nullable(); // اسم أو وصف الرخصة (مثال: رخصة كتاب الرياضيات)
            $table->enum('type', ['individual', 'group'])->default('individual');
            $table->enum('status', ['active', 'suspend', 'expired'])->default('active');

            $table->integer('max_devices')->default(1);
            $table->timestamp('registered_at')->nullable();

            $table->timestamp('valid_from')->nullable();
            $table->timestamp('valid_until')->nullable();
            $table->boolean('never_expires')->default(false);

            $table->boolean('send_via_email')->default(false);
            $table->string('file_path')->nullable();
            // تقييد الـ IP (استخدمنا JSON لأن العميل قد يُسمح له بأكثر من IP)
            // $table->json('restricted_ips')->nullable()->after('never_expires');
            // $table->boolean('auto_detect_ip')->default(false)->after('restricted_ips');

            // // تقييد الدولة (مثل: YE, SA, EG)
            // $table->json('restricted_countries')->nullable()->after('auto_detect_ip');
            // $table->boolean('auto_detect_country')->default(false)->after('restricted_countries');
            // $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customer_licenses');
    }
};
