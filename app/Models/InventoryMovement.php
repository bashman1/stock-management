<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'branch_id', 'institution_id', 'direction', 'quantity', 'unit_cost', 'total_cost',
        'method', 'source_type', 'ref_no', 'tran_id', 'created_by', 'created_on',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_cost' => 'float',
        'total_cost' => 'float',
    ];
}
