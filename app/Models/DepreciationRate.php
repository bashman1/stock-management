<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepreciationRate extends Model
{
    protected $fillable = [
        'name', 'rate', 'description', 'duration_id', 'status',
        'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    public function duration()
    {
        return $this->belongsTo(Duration::class);
    }
}
