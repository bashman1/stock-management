<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempProduct extends Model
{
    use HasFactory;

    protected $fillable = ['name','product_no', 'type_id', 'category_id', 'sub_category_id','manufacturer_id',
    'supplier_id', 'batch_id','measurement_unit_id', 'gauge_id', 'description', 'institution_id', 'user_id', 'purchase_price',
    'selling_price', 'discount', 'quantity', 'min_quantity', 'max_quantity', 'stock_date', 'manufactured_date',
    'expiry_date', 'tax_config', 'ref_no', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on'] ;
}
