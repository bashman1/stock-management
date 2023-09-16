<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable=['name', 'type', 'institution_id', 'status', 'description',
                         'created_by', 'updated_by', 'created_on', 'updated_on'];
}
