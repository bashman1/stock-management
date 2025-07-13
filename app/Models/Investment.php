<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable = [
        'project_id',
        'member_id',
        'quantity',
        'description',
        'date',
        'status',
        'institution_id',
        'branch_id',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on'
    ];
}
