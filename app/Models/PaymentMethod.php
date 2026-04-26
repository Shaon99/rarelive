<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use RecycleBinTrait;

    protected $guarded = [];

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }
}
