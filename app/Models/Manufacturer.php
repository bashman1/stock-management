<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    use HasFactory;

    protected $fillable=["name", "website", "email", "phone_number", "country_id",
        "institution_id", "branch_id", "user_id", "description", "status", "created_by",
        "updated_by", "created_on", "updated_on"];
}
