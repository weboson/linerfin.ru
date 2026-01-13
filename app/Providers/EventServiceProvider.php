<?php

namespace App\Providers;

use App\Models\Bill;
use App\Models\Counterparty;
use App\Observers\BillObserver;
use App\Observers\CounterpartyObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Bill::observe(BillObserver::class);
        Counterparty::observe(CounterpartyObserver::class);
    }
}
