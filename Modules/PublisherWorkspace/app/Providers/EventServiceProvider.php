<?php

namespace Modules\PublisherWorkspace\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\PublisherWorkspace\Events\PublisherLicenseCreated;
use Modules\PublisherWorkspace\Listeners\SendLicenseEmailListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
            // عندما يحدث هذا الحدث (إنشاء رخصة)...
        PublisherLicenseCreated::class => [
                // ...قم بتشغيل هذا المستمع (إرسال الإيميل)
            SendLicenseEmailListener::class,
        ],
    ];
    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void {}
}
