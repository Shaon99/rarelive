<?php

namespace App\Listeners;

use App\Events\ProductLowStock;
use App\Models\Admin;
use App\Notifications\LowStockNotification;

class NotifyAdminLowStock
{
    public function handle(ProductLowStock $event)
    {
        $admins = Admin::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->get();

        foreach ($admins as $admin) {
            $admin->notify(new LowStockNotification($event->product));
        }
    }
}
