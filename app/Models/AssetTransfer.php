<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetTransfer extends Model
{
    protected $fillable = [
        'asset_id', 'from_branch_id', 'to_branch_id', 'from_location', 'to_location',
        'transfer_date', 'reason', 'transferred_by', 'status',
        'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];
}
