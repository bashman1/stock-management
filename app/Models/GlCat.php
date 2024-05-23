<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlCat extends Model
{
    use HasFactory;
    protected $fillable=['gl_no', 'acct_type', 'description', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
