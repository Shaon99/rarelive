<?php

namespace App\Notifications;

use App\Models\Sales;
use Illuminate\Notifications\Notification;

class SaleNotification extends Notification
{
    public $sale;

    // Constructor to pass the sale object
    public function __construct(Sales $sale)
    {
        $this->sale = $sale;
    }

    // Define the notification delivery channels
    public function via($notifiable)
    {
        return ['database'];
    }

    // Define the notification data for the database
    public function toDatabase($notifiable)
    {
        return [
            'message' => "A new sale has been created! by: {$this->sale->user->name}. Invoice: {$this->sale->invoice_no}",
            'link' => route('admin.invoice', $this->sale->id),
        ];
    }
}
