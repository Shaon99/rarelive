<?php

namespace App\Events;

use App\Models\Sales;
use Illuminate\Queue\SerializesModels;

class SaleCreated
{
    use SerializesModels;

    public $sale;

    public function __construct(Sales $sale)
    {
        $this->sale = $sale;
    }
}
