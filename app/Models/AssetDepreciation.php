<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDepreciation extends Model
{
    protected $fillable = [
        'asset_id', 'period', 'method', 'opening_book_value', 'depreciation_amount',
        'accumulated_after', 'closing_book_value', 'run_date', 'tran_id', 'status',
        'institution_id', 'branch_id', 'created_by', 'created_on',
    ];
}
