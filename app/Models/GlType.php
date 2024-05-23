<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlType extends Model
{
    use HasFactory;

    protected $fillable=["gl_no", "description", "gl_cat_no", "acct_type", "gl_sub_cat_no", "status", "created_by",
                        "updated_by", "created_on", "updated_on"];
}
