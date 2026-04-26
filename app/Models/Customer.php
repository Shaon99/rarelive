<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory,RecycleBinTrait;

    protected $guarded = [];

    public function scopeSearch($query, $value): void
    {
        $query->where('name', 'like', "%{$value}%")
            ->orWhere('phone', 'like', "%{$value}%")
            ->orWhere('email', 'like', "%{$value}%");
    }

    public function addressBooks()
    {
        return $this->hasMany(AddressBook::class, 'customer_id', 'id');
    }
}
