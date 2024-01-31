<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CityRef extends Model
{
    use HasFactory;
    protected $fillable = ['country_id', 'name', 'code', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
