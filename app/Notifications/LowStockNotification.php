<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification
{
    protected $product;

    public function __construct($product)
    {
        $this->product = $product;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'message' => "The product '{$this->product->name}' is running low on stock. Current quantity: {$this->product->quantity}.",
            'link' => route('admin.product.edit', $this->product->id),
        ];
    }
}
