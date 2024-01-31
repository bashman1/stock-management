<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionConfig extends Model
{
    use HasFactory;

    protected $fillable=['type', 'prefix', 'starting', 'current', 'step', 'status',
    'description', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
