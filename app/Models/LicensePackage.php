<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicensePackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on'
    ];
}
