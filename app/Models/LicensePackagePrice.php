<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicensePackagePrice extends Model
{
    protected $fillable = [
        'license_package_id',
        'duration_id',
        'amount',
        'status', // active, inactive, archived
        'created_by',
        'created_on',
        'updated_by',
        'updated_on'
    ];

}
