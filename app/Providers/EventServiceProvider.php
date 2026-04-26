<?php

namespace App\Providers;

use App\Events\ProductLowStock;
use App\Events\SaleCreated;
use App\Listeners\NotifyAdminLowStock;
use App\Listeners\SendSaleNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        ProductLowStock::class => [
            NotifyAdminLowStock::class,
        ],
        SaleCreated::class => [
            SendSaleNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot() {}
}
