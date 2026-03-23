<?php

namespace Modules\PublisherWorkspace\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // للإرسال في الخلفية (الطوابير)
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class LicenseGeneratedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $lzpkFileData;

    // نستلم الملف المشفر فقط (لأن بيانات الناشر موجودة تلقائياً في $notifiable)
    public function __construct(string $lzpkFileData)
    {
        $this->lzpkFileData = $lzpkFileData;
    }

    public function via($notifiable)
    {
        return ['mail']; // مستقبلاً لو أردنا إضافة واتساب، نكتب 'mail', 'whatsapp' هنا فقط!
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('مرحباً بك في نظام الحماية - رخصة الاستخدام الخاصة بك')

            // السحر هنا: نربط قالب الـ HTML الذي صممناه، ونمرر له بيانات الناشر
            ->view('publisherworkspace::emails.publisher_license', [
                'publisher' => $notifiable
            ])

            // إرفاق ملف الـ LZPK من الذاكرة مباشرة
            ->attachData( base64_decode($this->lzpkFileData), 'license_' . $notifiable->id . '.lic', [
                'mime' => 'application/octet-stream',
            ]);
    }
}
