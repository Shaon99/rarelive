<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use RecycleBinTrait;

    protected $guarded = [];

    public function transactionable()
    {
        return $this->morphTo();
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id', 'id');
    }

    public function fromAccount()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function toAccount()
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
