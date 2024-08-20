<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payable extends Model
{
    use HasFactory;

    protected $fillable = ["ref_no", "tran_id", "supplier_id", "product_id", "manufacturer_id",
"amount", "amount_paid", "status", "institution_id", "branch_id", "created_by", "updated_by", "created_on", "updated_on"];
}
