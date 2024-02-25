<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable=["order_id", "product_id", "qty", "status", "institution_id", "branch_id", "created_by", "updated_by", "created_on", "updated_on"];
}
