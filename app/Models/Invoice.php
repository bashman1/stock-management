<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'name',
        'invoice_type',
        'ref_no',
        'invoice_no',
        'item_count',
        'total',
        'discount',
        'amount_paid',
        'user_id',
        'customer_id',
        'invoice_date',
        'status',
        'address',
        'payment_status',
        'institution_id',
        'branch_id',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on'
    ];
}
