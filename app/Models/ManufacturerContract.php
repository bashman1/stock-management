<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturerContract extends Model
{
    use HasFactory;

    protected $fillable = [
        'manufacturer_id', 'title', 'contract_no', 'start_date', 'end_date', 'value', 'status',
        'file_path', 'notes', 'institution_id', 'branch_id', 'created_by', 'created_on',
    ];

    protected $casts = ['value' => 'float'];
}
