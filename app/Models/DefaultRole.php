<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DefaultRole extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'status', 'role_type', 'description', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
