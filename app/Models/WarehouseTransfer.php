<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref_no', 'product_id', 'from_warehouse_id', 'to_warehouse_id', 'from_location_id', 'to_location_id',
        'quantity', 'transfer_date', 'reason', 'transferred_by', 'status',
        'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];
}
