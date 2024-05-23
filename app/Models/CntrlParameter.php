<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CntrlParameter extends Model
{
    use HasFactory;

    protected $fillable=['param_name','param_cd', 'param_value', 'status', 'institution_id', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
