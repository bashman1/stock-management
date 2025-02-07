<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerReceivable extends Model
{
    use HasFactory;
    protected $fillable = ['ref_no', 'receipt_no', 'tran_id', 'customer_id', 'amount',
    'amount_paid', 'status', 'institution_id', 'branch_id', 'created_by', 'updated_by',
    'created_on', 'updated_on'] ;
}
