<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TempSale extends Model
{
    use HasFactory;

    protected $fillable=['ref_no', 'status', 'stock_date', 'institution_id', 'created_by', 'updated_by',
        'created_on', 'updated_on', 'batch_id', 'branch_id', 'quantity'];
}

