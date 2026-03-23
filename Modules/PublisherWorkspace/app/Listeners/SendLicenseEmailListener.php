<?php

namespace Modules\PublisherWorkspace\Listeners;

use Modules\PublisherWorkspace\Events\PublisherLicenseCreated;
use Modules\PublisherWorkspace\Notifications\LicenseGeneratedNotification;

class SendLicenseEmailListener
{
    public function handle(PublisherLicenseCreated $event)
    {
        // نأمر النظام بإرسال الإشعار للناشر
        $event->publisher->notify(new LicenseGeneratedNotification($event->lzpkFileData));
    }
}
