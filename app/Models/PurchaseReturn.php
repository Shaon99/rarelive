<?php

// app/Models/PurchaseReturn.php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    use HasFactory, RecycleBinTrait;

    protected $guarded = [];

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    // Relationship with Purchase
    public function purchase()
    {
        return $this->belongsTo(Purchases::class);
    }

    // created_by relationship
    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    // Relationship with PurchaseReturnItems
    public function purchaseReturnItems()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }
}
