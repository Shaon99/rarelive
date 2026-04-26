<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory, RecycleBinTrait;

    protected $casts = [
        'meaning' => 'array',
    ];

    protected $guarded = [];
}
