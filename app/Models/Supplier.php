<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory, RecycleBinTrait;

    protected $guarded = [];

    public function purchases()
    {
        return $this->hasMany(Purchases::class);
    }
}
