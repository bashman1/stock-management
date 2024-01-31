<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberBatch extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'status', 'institution_id', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
