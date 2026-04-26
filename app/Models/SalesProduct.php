<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesProduct extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function combo()
    {
        return $this->belongsTo(Combo::class, 'combo_id', 'id');
    }

    public function sale()
    {
        return $this->belongsTo(Sales::class, 'sales_id', 'id');
    }
}
