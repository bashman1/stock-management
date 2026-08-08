<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_no', 'bom_id', 'product_id', 'quantity', 'quantity_completed', 'quantity_scrapped',
        'wip_cost', 'status', 'scheduled_date', 'started_on', 'completed_on', 'tran_id',
        'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    protected $casts = [
        'quantity' => 'float',
        'quantity_completed' => 'float',
        'quantity_scrapped' => 'float',
        'wip_cost' => 'float',
    ];

    public function materials()
    {
        return $this->hasMany(ProductionOrderMaterial::class, 'production_order_id', 'id');
    }

    public function movements()
    {
        return $this->hasMany(ProductionMovement::class, 'production_order_id', 'id');
    }

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
