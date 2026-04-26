<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Model;

class Combo extends Model
{
    use RecycleBinTrait;

    protected $fillable = ['name', 'price', 'quantity'];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity')->withTimestamps();
    }

    public function calculatePrice()
    {
        return $this->products->sum(function ($product) {
            return $product->sale_price * $product->pivot->quantity;
        });
    }
}
