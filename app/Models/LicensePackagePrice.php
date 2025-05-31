<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicensePackagePrice extends Model
{
    //
    protected $fillable = [
        'license_package_id',
        'price',
    ];
}
