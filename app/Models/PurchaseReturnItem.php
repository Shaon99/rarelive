<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturnItem extends Model
{
    use HasFactory, RecycleBinTrait;

    protected $guarded = [];

    // Relationship with PurchaseReturn
    public function purchaseReturn()
    {
        return $this->belongsTo(PurchaseReturn::class);
    }

    // Relationship with PurchaseProduct
    public function purchaseProduct()
    {
        return $this->belongsTo(PurchasesProduct::class);
    }

    // product relationship
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
