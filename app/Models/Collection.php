<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    use HasFactory;
    protected $fillable=["amount", "description", "tran_date", "member_number",
        "member_id", "institution_id", "branch_id", "user_id", "tran_id", "status",
        "tran_cd","tran_indicator","temp_collection_id","created_by", "updated_by", "created_on", "updated_on"];
}
