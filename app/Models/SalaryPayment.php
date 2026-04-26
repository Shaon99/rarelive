<?php

namespace App\Models;

use App\Traits\RecycleBinTrait;
use Illuminate\Database\Eloquent\Model;

class SalaryPayment extends Model
{
    use RecycleBinTrait;

    protected $fillable = [
        'salary_id',
        'employee_id',
        'amount_paid',
        'payment_method',
        'note',
    ];

    public function salary()
    {
        return $this->belongsTo(Salary::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method', 'id');
    }
}
