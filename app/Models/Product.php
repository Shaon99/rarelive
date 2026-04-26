<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, RecycleBinTrait;

    protected $guarded = [];

    public function productGallery()
    {
        return $this->hasMany(ProductImageGallery::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function warehouses()
    {
        return $this->hasMany(WarehouseProducts::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockProduct()
    {
        return $this->hasMany(PurchasesProduct::class, 'product_id', 'id');
    }

    public function salesProducts()
    {
        return $this->hasMany(SalesProduct::class, 'product_id', 'id');
    }

    public function combos()
    {
        return $this->belongsToMany(Combo::class)->withPivot('quantity')->withTimestamps();
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchasesProduct::class);
    }

    public function returnItems()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function stockTransactions()
    {
        return $this->hasMany(StockTransaction::class);
    }

    public function variations()
    {
        return $this->hasMany(ProductVariation::class);
    }
}
