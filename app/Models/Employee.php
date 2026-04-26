<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use RecycleBinTrait;

    protected $guarded = [];
}
