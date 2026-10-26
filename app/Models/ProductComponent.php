<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'bundle_product_id', 'component_product_id', 'quantity', 'measurement_unit_id',
        'institution_id', 'branch_id', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    public function component()
    {
        return $this->hasOne(Product::class, 'id', 'component_product_id');
    }
}
