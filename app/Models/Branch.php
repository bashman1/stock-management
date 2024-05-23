<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $fillable=['name', 'institution_id', 'address', 'city_id', 'street', 'p_o_box',
    'description', 'status', "code",'is_main', 'created_by','updated_by', 'created_on','updated_on'];
}
