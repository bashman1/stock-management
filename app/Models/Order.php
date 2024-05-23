<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable=["ref_no", "receipt_no", "tran_id", "item_count", "total", "discount", "amount_paid", "user_id",
                        "customer_id", "tran_date", "status", "payment_status", "institution_id", "branch_id", "created_by",
                        "updated_by", "created_on", "updated_on"];
}
