<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    //

    protected $fillable = [
        'invoice_id',
        'product_id',
        'qty',
        'base_qty',
        'status',
        'selling_price',
        'purchase_price',
        'institution_id',
        'measurement_unit_id',
        'branch_id',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on'
    ];
}
