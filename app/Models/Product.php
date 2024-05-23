<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable=["name", "product_no", "category_id", "sub_category_id", "manufacturer_id", "supplier_id", "measurement_unit_id",
        "description", "institution_id", "user_id", "status", "created_by", "updated_by", "created_on", "updated_on", "type_id", "gauge_id", "ref_no", "tax_config"];
}
