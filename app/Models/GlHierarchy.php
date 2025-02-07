<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlHierarchy extends Model
{
    use HasFactory;

    protected $fillable = ["acct_no", "parent_acct_no", "status", "institution_id", "branch_id", "created_by", "updated_by", "created_on", "updated_on"];
}
