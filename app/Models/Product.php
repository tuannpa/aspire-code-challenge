<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Used for mass assignment
    protected $fillable = [
        'name',
        'type',
        'amount',
        'minimum_credit_point_requirement',
        'description'
    ];
}
