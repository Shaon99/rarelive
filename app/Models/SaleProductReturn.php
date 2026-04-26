<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SaleProductReturn extends Model
{
    protected $guarded = [];

    public function items()
    {
        return $this->hasMany(SaleProductReturn::class, 'sale_id', 'sale_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function sale()
    {
        return $this->belongsTo(Sales::class);
    }
}
