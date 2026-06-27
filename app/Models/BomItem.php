<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bom_id', 'material_product_id', 'quantity', 'measurement_unit_id', 'scrap_percent',
        'institution_id', 'branch_id', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    protected $casts = [
        'quantity' => 'float',
        'scrap_percent' => 'float',
    ];

    public function material()
    {
        return $this->hasOne(Product::class, 'id', 'material_product_id');
    }
}
