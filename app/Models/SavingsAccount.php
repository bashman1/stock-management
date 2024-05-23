<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingsAccount extends Model
{
    use HasFactory;
    protected $fillable=["member_number", "balance", "member_id", "institution_id", "branch_id", "acct_prod_id",
                        "user_id", "status", "created_by", "updated_by", "created_on", "updated_on"];
}
