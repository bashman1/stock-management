<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        "name", "product_no", "category_id", "sub_category_id", "manufacturer_id", "supplier_id", "measurement_unit_id",
        "description", "institution_id", "user_id", "status", "created_by", "updated_by", "created_on", "updated_on",
        "type_id", "gauge_id", "ref_no", "tax_config", "secondary_measurement_unit_id", "secondary_weight", "secondary_price",
        "nature", "tracks_stock", "valuation_method", "standard_cost"
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
        return $this->hasOne(stock::class, 'product_id', 'id');
    }

    public function units()
    {
        return $this->hasMany(ProductUnit::class, 'product_id', 'id');
    }

    public function baseUnit()
    {
        return $this->hasOne(ProductUnit::class, 'product_id', 'id')->where('is_base', true);
    }

    public function components()
    {
        return $this->hasMany(ProductComponent::class, 'bundle_product_id', 'id');
    }
}
