<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable=['name', 'email', 'phone_number', 'address', 'status', 'description', 'user_id', 'branch_id',
        'institution_id', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
