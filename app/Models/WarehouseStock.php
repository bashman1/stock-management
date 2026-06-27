<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id', 'location_id', 'product_id', 'quantity',
        'institution_id', 'branch_id', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
