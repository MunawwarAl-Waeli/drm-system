<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

// استيراد الموديلات من الموديولات المختلفة حسب الأكواد التي أرسلتها
use Modules\PublisherWorkspace\Models\Publisher;
use Modules\Library\Models\Publication;
use Modules\Library\Models\Document;
use Modules\Library\Models\DocumentKey;
use Modules\Library\Models\DocumentSecurityControl;

class FastFillLibrary extends Command
{
    // الاسم الذي ستستخدمه في التيرمينال
    protected $signature = 'library:fill';
    protected $description = 'تعبئة المكتبة ببيانات تجريبية (ناشر، منشورات، مستندات مع مفاتيحها وقيودها)';

    public function handle()
    {
        $this->info('--- جاري بدء عملية التعبئة الذكية ---');

        // 1. إنشاء أو جلب ناشر تجريبي
        $publisher = Publisher::updateOrCreate(
            ['email' => 'publisher@test.com'],
            [
                'name' => 'ناشر تجريبي احترافي',
                'password' => Hash::make('password'),
                'company' => 'شركة التقنية للنشر',
                'status' => 'active',
                'ecommerce_key' => Str::random(16)
            ]
        );
        $this->line('✅ تم تجهيز الناشر: ' . $publisher->name);

        // 2. إنشاء منشور (Publication)
        $publication = Publication::create([
            'name' => 'المجموعة التعليمية لعام 2024',
            'description' => 'مجموعة تحتوي على ملفات تعليمية مشفرة',
            'obey' => true,
            'publisher_id' => $publisher->id,
        ]);
        $this->line('✅ تم إنشاء المنشور: ' . $publication->name);

        // 3. إنشاء مستند (Document) داخل المنشور
        $document = Document::create([
            'document_uuid' => Str::uuid(),
            'title' => 'كتاب البرمجة المتقدمة',
            'type' => 'pdf',
            'description' => 'ملف تعليمي مشفر بالكامل',
            'size' => 5120, // 5MB
            'file_hash' => hash('sha256', 'sample_data'),
            'original_filename' => 'advanced_programming.pdf',
            'status' => 'valid',
            'access_scope' => 'selected_customers',
            'publisher_id' => $publisher->id,
            'publication_id' => $publication->id,
        ]);
        $this->line('✅ تم إضافة المستند الرئيسي');

        // 4. إنشاء مفتاح المستند (Document Key) - علاقة 1 إلى 1
        DocumentKey::create([
            'document_id' => $document->id,
            'encrypted_key' => base64_encode(Str::random(32)),
            'key_version' => 1,
            'is_active' => true,
        ]);
        $this->line('🔑 تم توليد مفتاح التشفير للمستند');

        // 5. إنشاء قيود الحماية (Security Controls) - علاقة 1 إلى 1
        DocumentSecurityControl::create([
            'document_id' => $document->id,
            'expiry_mode' => 'fixed_date',
            'expiry_date' => now()->addMonths(6),
            'verify_mode' => 'never',
            'verify_frequency_days' => 0,
            'grace_period_days' => 0,
            'max_views_allowed' => 100,
        ]);
        $this->line('🛡️ تم ضبط قيود الحماية والانتهاء');

        $this->newLine();
        $this->info('🚀 تمت العملية بنجاح! الآن جداولك مليئة بالبيانات المترابطة.');
    }
}
