<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable=["order_id", "product_id", "qty", "status", "institution_id", "branch_id", "created_by", "updated_by", "created_on", "updated_on"];

    public function product()
    {
        return $this->hasOne(Product::class, 'id','product_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function stock()
    {
        return $this->hasOne(Stock::class, 'product_id', 'product_id');
    }

    public function institution()
    {
        return $this->hasOne(Institution::class, 'id', 'institution_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'created_by');
    }
}
