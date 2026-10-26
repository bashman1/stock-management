<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id', 'type', 'product_id', 'quantity', 'cost', 'tran_id', 'ref_no', 'note',
        'institution_id', 'branch_id', 'created_by', 'created_on',
    ];

    protected $casts = [
        'quantity' => 'float',
        'cost' => 'float',
    ];
}
