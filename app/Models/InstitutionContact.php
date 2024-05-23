<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionContact extends Model
{
    use HasFactory;
    protected $fillable=['name', 'phone_number', 'email', 'website', 'status',
    'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
