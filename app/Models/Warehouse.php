<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory, RecycleBinTrait;

    protected $guarded = [];

    public function warehouseProducts()
    {
        return $this->hasMany(WarehouseProducts::class);
    }
}
