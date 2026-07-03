<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturerDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufacturer_id', 'name', 'type', 'file_path', 'expiry_date', 'status',
        'institution_id', 'branch_id', 'created_by', 'created_on',
    ];
}
