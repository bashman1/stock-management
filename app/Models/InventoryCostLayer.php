<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryCostLayer extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'branch_id', 'institution_id', 'source_type', 'source_ref', 'receipt_date',
        'quantity', 'remaining_quantity', 'unit_cost', 'status',
        'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    protected $casts = [
        'quantity' => 'float',
        'remaining_quantity' => 'float',
        'unit_cost' => 'float',
    ];
}
