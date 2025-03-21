<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class stock extends Model
{
    use HasFactory;

    protected $fillable = ["purchase_price", "selling_price", "discount", "product_id",
    "quantity", "min_quantity", "max_quantity", "institution_id", "stock_date", 'manufactured_date','expiry_date',"branch_id",
    "user_id", "status", "created_by", "updated_by", "created_on", "updated_on"];
}
