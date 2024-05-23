<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CollectionHistory extends Model
{
    use HasFactory;
    protected $fillable=["amount","description","member_number", "member_id", "tran_id",
                        "tran_cd", "tran_indicator", "institution_id", "branch_id", "user_id",
                        "collection_id", "status", "created_by", "updated_by", "created_on", "updated_on"];
}
