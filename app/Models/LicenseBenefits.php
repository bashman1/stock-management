<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LicenseBenefits extends Model
{
    protected $table = 'license_benefits';

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
