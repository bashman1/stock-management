<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrderMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'production_order_id', 'material_product_id', 'quantity_required', 'quantity_consumed', 'unit_cost',
        'institution_id', 'branch_id', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    protected $casts = [
        'quantity_required' => 'float',
        'quantity_consumed' => 'float',
        'unit_cost' => 'float',
    ];

    public function material()
    {
        return $this->hasOne(Product::class, 'id', 'material_product_id');
    }
}
