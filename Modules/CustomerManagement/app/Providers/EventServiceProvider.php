<?php

namespace Modules\CustomerManagement\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\CustomerManagement\Events\CustomerLicenseCreated;
use Modules\CustomerManagement\Listeners\SendCustomerLicenseEmailListener;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        CustomerLicenseCreated::class=>[
            SendCustomerLicenseEmailListener::class,
        ]
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
