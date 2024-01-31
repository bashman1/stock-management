<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavingAccountProduct extends Model
{
    use HasFactory;

    protected $fillable=["name", "description", "balance", "min_balance", "withdraw_allowed",
                        "overdraw_allowed","is_default", "currency", "institution_id", "branch_id", "user_id",
                        "status", "created_by", "updated_by", "created_on", "updated_on" ];
}
