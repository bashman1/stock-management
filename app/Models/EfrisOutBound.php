<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EfrisOutBound extends Model
{
    protected $fillable = [
        'trnsaction_type',
        'reference_no',
        'payload',
        'response',
        'order_id',
        'invoice_id',
        'product_id',
        'stock_id',
        'stock_history_id',
        'status',
        'is_sent',
        'sent_at',
        'uri',
        'http_method',
        'institution_id',
        'branch_id',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on',
    ];

    protected $casts = [
        'payload'  => 'array',
        'response' => 'array',
    ];
}
