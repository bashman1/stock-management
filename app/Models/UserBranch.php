<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBranch extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'branch_id', 'status', 'institution_id', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
