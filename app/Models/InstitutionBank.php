<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionBank extends Model
{
    use HasFactory;
    protected $fillable=['bank_id', 'acct_name', 'acct_number', 'status', 
    'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
