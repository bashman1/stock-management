<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitutionLicense extends Model
{
    protected $fillable = [
        'institution_id',
        'license_package_id',
        'license_package_price_id',
        'start_date',
        'end_date',
        'quantity',
        'paid',
        'status', // active, inactive, archived
        'created_by',
        'created_on',
        'updated_by',
        'updated_on'
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function licensePackage()
    {
        return $this->belongsTo(LicensePackage::class);
    }
}
