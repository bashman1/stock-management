<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedAsset extends Model
{
    protected $fillable = [
        'ref_no', 'name', 'description', 'asset_account', 'expense_account', 'supplier_id',
        'depreciation_rate_id', 'depreciation_method', 'purchase_amount', 'salvage_value',
        'accumulated_depreciation', 'book_value', 'last_depreciation_date',
        'payment_method', 'pay_acct_no', 'payment_method_id', 'duration_id',
        'status', 'purchase_date', 'location', 'tran_id',
        'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    public function depreciationRate()
    {
        return $this->belongsTo(DepreciationRate::class, 'depreciation_rate_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function transfers()
    {
        return $this->hasMany(AssetTransfer::class, 'asset_id');
    }

    public function maintenances()
    {
        return $this->hasMany(AssetMaintenance::class, 'asset_id');
    }

    public function depreciations()
    {
        return $this->hasMany(AssetDepreciation::class, 'asset_id');
    }
}
