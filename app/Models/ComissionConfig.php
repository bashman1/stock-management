<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComissionConfig extends Model
{
    use HasFactory;
    protected $fillable=["name", "amount", "commission_type", "institution_id", "branch_id",
                        "user_id", "status", "created_by", "updated_by", "created_on", "updated_on"];
}
