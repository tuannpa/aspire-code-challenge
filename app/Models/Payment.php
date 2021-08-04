<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    // Used for mass assignment.
    protected $fillable = [
        'customer_id',
        'loan_id',
        'state',
        'due_date',
        'repaid_date',
        'paid_amount',
        'remaining_amount'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
}
