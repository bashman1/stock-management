<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    //

    protected $fillable = [
        'name',
        'city_id',
        'street',
        'address',
        'plot',
        'status',
        'rent',
        'institution_id',
        'branch_id',
        'description',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on'
    ];
}
