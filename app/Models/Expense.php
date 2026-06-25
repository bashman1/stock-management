<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref_no', 'expense_acct_no', 'category_name', 'amount', 'payment_method',
        'pay_acct_no', 'payee', 'supplier_id', 'description', 'expense_date',
        'receipt_path', 'tran_id', 'payment_status', 'paid_tran_id', 'paid_on',
        'status', 'institution_id', 'branch_id', 'user_id',
        'created_by', 'updated_by', 'created_on', 'updated_on',
    ];
}
