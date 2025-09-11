<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempRental extends Model
{
    protected $fillable = [
        'building_id',
        'unit_id',
        'member_id',
        'duration',
        'amount_paid',
        'status',
        'batch_id',
        'ref_no',
        'rental_date',
        'due_date',
        'paid_date',
        'description',
        'institution_id',
        'branch_id',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on',
        'payment_status',
    ];
}
