<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturerContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufacturer_id', 'name', 'title', 'email', 'phone', 'is_primary',
        'institution_id', 'branch_id', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    protected $casts = ['is_primary' => 'boolean'];
}
