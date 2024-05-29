<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        "name", "product_no", "category_id", "sub_category_id", "manufacturer_id", "supplier_id", "measurement_unit_id",
        "description", "institution_id", "user_id", "status", "created_by", "updated_by", "created_on", "updated_on", "type_id", "gauge_id", "ref_no", "tax_config"
    ];


    public function category()
    {
        return $this->hasOne(ProductCategory::class, 'id','category_id');
    }

    public function subCategory()
    {
        return $this->hasOne(ProductSubCategory::class, 'id', 'sub_category_id');
    }

    public function measurement()
    {
        return $this->hasOne(measurementUnit::class, 'id', 'measurement_unit_id');
    }

    public function productType()
    {
        return $this->hasOne(ProductType::class, 'id', 'type_id');
    }

    public function stock()
    {
        return $this->belongsTo(stock::class, 'id', 'product_id');
    }
}
