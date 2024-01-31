<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberNoConfig extends Model
{
    use HasFactory;

    protected $fillable=['prefix','institution_id_code', 'start_from','current_value', 'institution_id',
                        'created_by','updated_by','created_on','updated_on'];
}
