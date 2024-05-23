<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlBalances extends Model
{
    use HasFactory;

    protected $fillable = ['acct_no', 'acct_type', 'balance', 'branch_cd', 'status', 'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
