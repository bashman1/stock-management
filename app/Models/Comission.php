<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comission extends Model
{
    use HasFactory;
    protected $fillable=["tran_id", "amount", "commission_config_id","institution_id","branch_id",
                            "user_id","status","created_by","updated_by", "created_on","updated_on"];
}
