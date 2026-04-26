<?php

namespace App\Listeners;

use App\Events\SaleCreated;
use App\Models\Admin;
use App\Notifications\SaleNotification;

class SendSaleNotification
{
    public function handle(SaleCreated $event)
    {
        $admins = Admin::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        foreach ($admins as $admin) {
            $admin->notify(new SaleNotification($event->sale));
        }
    }
}
