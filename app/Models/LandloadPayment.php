<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandloadPayment extends Model
{
    protected $fillable = [
        'building_id',
        'amount',
        'payment_type',
        'payment_date',
        'payment_method',
        'description',
        'status',
        'institution_id',
        'branch_id',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on',
    ];
}
