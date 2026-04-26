<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use RecycleBinTrait;

    protected $guarded = [];

    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'transactionable');
    }

    public function payments()
    {
        return $this->hasMany(SalaryPayment::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
