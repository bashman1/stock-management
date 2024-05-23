<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasFactory;
    protected $fillable=['first_name', 'last_name', 'other_name',
    'phone_number', 'member_number', 'alt_member_number', 'gender',
    'date_of_birth','address','city_id','institution_id', 'branch_id',
    'status', 'street', 'p_o_box', 'description', 'created_by', 'updated_by',
    'created_on', 'updated_on'];
}
