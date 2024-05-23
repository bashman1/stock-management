<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankRef extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
