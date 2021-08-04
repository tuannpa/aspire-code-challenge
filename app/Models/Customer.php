<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    // Used for mass assignment.
    protected $fillable = [
        'name',
        'phone_number',
        'address',
        'gender',
        'credit_point',
        'date_of_birth',
        'product_id'
    ];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
