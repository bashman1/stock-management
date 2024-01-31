<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginSession extends Model
{
    use HasFactory;

    protected $fillable=['user_id', 'token', 'status', 'locked',
    'logged_in_at', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
