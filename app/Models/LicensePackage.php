<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicensePackage extends Model
{
    protected $fillable = [
        'name',
        'implementation',
        'description',
        'status', // active, inactive, archived
        'created_by',
        'updated_by',
        'created_on',
        'updated_on'
    ];

    public function prices()
    {
        return $this->hasMany(LicensePackagePrice::class);
    }

    public function benefits()
    {
        return $this->belongsToMany(LicenseBenefits::class, 'license_package_benefits'); // assuming many-to-many
    }
}
