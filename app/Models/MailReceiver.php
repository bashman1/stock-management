<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailReceiver extends Model
{
    //
    protected $fillable=["name", "email", "category",
    "description", "user_id", "institution_id", "branch_id",
"status", "created_by", "updated_by", "created_on", "updated_on"];
}
