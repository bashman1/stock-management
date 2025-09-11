<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempBuildings extends Model
{
    protected $fillable = [
        'name',
        'city_id',
        'street',
        'address',
        'batch_no',
        'plot',
        'status',
        'rent',
        'description',
        'institution_id',
        'branch_id',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on'
    ];
}
