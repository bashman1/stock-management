<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable=[
        'name',
        'description',
        'duration_id',
        'benefit_type',
        'benefit',
        'monitory',
        'date',
        'status',
        'institution_id',
        'branch_id',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on'
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    public function duration()
    {
        return $this->belongsTo(Duration::class);
    }
}
