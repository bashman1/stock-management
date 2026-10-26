<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Duration extends Model
{
    protected $fillable = [
        'name',
        'value',
        'unit',
        'description',
        'status',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on'
    ];


}
