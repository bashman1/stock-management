<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EfrisMeasurementUnits extends Model
{
    protected $table = 'efris_measurement_units';

    protected $fillable = [
        'name',
        'description',
        'value',
        'user_id',
        'status',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on',
    ];
}
