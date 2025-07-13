<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    protected $fillable = ['name', 'description', 'asset_account', 'expense_account', 'depreciation_rate_id', 'purchase_amount', 'payment_method_id', 'duration_id', 'status', 'purchase_date', 'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on'];

}
