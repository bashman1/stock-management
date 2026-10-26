<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicensePackageBenefit extends Model
{
    protected $table = 'license_package_benefits';

    protected $fillable = [
        'license_package_id',
        'license_benefit_id',
        'status',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on'
    ];

}
