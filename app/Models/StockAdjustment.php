<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref_no', 'type', 'reason', 'total_cost', 'tran_id', 'status', 'adjustment_date',
        'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    public function items()
    {
        return $this->hasMany(StockAdjustmentItem::class, 'stock_adjustment_id', 'id');
    }
}
