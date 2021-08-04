<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    // Used for mass assignment.
    protected $fillable = [
        'customer_id',
        'interest_rate',
        'description',
        'status',
        'duration',
        'amount',
        'approved_by',
        'product_id'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function payment()
    {
        return $this->hasOne(Payment::class);
    }
}
