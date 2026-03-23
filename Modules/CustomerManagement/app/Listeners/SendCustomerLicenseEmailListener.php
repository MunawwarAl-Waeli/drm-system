<?php

namespace Modules\CustomerManagement\Listeners;

use Illuminate\Support\Facades\Notification;
use Modules\CustomerManagement\Events\CustomerLicenseCreated;
use Modules\CustomerManagement\Notifications\CustomerLicenseGeneratedNotification;

class SendCustomerLicenseEmailListener
{
    public function handle(CustomerLicenseCreated $event)
    {
        // بما أن كائن CustomerLicense قد لا يحتوي على ترايت Notifiable،
        // نستخدم واجهة Notification لتوجيه الإيميل مباشرة إلى حقل email في الرخصة
        Notification::route('mail', $event->license->email)
            ->notify(new CustomerLicenseGeneratedNotification(
                $event->license,
                $event->encodedFileData,
                $event->fileName
            ));
    }
}
