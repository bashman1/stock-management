<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id', 'customer_group_id', 'product_id', 'price',
        'institution_id', 'branch_id', 'status', 'created_by', 'created_on',
    ];

    protected $casts = ['price' => 'float'];

    public function product()
    {
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
