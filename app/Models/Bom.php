<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bom extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id', 'name', 'version', 'output_quantity', 'is_active',
        'institution_id', 'branch_id', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    protected $casts = [
        'output_quantity' => 'float',
        'is_active' => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(BomItem::class, 'bom_id', 'id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
