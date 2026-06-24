<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        "product_id", "measurement_unit_id", "conversion_factor", "purchase_price",
        "selling_price", "is_base", "institution_id", "branch_id", "status",
        "created_by", "updated_by", "created_on", "updated_on"
    ];

    protected $casts = [
        'is_base' => 'boolean',
        'conversion_factor' => 'float',
        'purchase_price' => 'float',
        'selling_price' => 'float',
    ];

    public function measurement()
    {
        return $this->hasOne(measurementUnit::class, 'id', 'measurement_unit_id');
    }
}
