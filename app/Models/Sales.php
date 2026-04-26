<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sales extends Model
{
    use HasFactory, RecycleBinTrait;

    protected $guarded = [];

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(Admin::class);
    }

    public function lead()
    {
        return $this->belongsTo(Employee::class, 'lead_id');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function salesProduct()
    {
        return $this->hasMany(SalesProduct::class, 'sales_id', 'id');
    }

    public function scopeSearch($query, $value): void
    {
        $query->where('invoice_no', 'like', "%{$value}%")
            ->orWhere('consignment_id', 'like', "%{$value}%")
            ->orWhereHas('customer', function ($q) use ($value) {
                $q->where('name', 'like', "%{$value}%")
                    ->orWhere('phone', 'like', "%{$value}%");
            });
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method', 'id');
    }
}
