<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RentalBatch extends Model
{
    protected $fillable = [
        'name',
        'status',
        'institution_id',
        'branch_id',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on'
    ];
}
