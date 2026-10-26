<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempBuildingUnit extends Model
{
    protected $fillable = [
        'name',
        'building_id',
        'unit_type',
        'unit_no',
        'status',
        'batch_id',
        'ref_no',
        'rent',
        'description',
        'institution_id',
        'branch_id',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on'
    ];
}
