<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EfrisComodityCategory extends Model
{
    protected $table = 'efris_comodity_categories';

    protected $fillable = [
        'commodity_category_code',
        'commodity_category_level',
        'commodity_category_name',
        'enable_status_code',
        'excisable',
        'is_leaf_node',
        'vat_out_scope',
        'parent_code',
        'source_now_time',
        'status',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on',
    ];
}
