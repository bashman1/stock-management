<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory;

    protected $fillable=['name', 'institution_type_id', 'ref_no', 'start_date', 'address','city_id', 'street',
     'p_o_box', 'description', 'is_tax_enabled', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on'
    ];
}
