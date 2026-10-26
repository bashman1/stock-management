<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetMaintenance extends Model
{
    protected $fillable = [
        'asset_id', 'ref_no', 'maintenance_date', 'type', 'description', 'cost', 'payee',
        'expense_acct_no', 'payment_method', 'pay_acct_no', 'tran_id', 'status',
        'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];
}
