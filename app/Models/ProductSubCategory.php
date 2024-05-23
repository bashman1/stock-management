<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSubCategory extends Model
{
    use HasFactory;

    protected $fillable=["name", "description","category_id", "institution_id", "branch_id",
    "user_id", "status", "created_by", "updated_by", "created_on", "updated_on"];
}
