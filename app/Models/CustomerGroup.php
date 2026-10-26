<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'discount_percent', 'description', 'institution_id', 'status',
        'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    protected $casts = ['discount_percent' => 'float'];
}
