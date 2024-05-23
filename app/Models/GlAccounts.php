<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlAccounts extends Model
{
    use HasFactory;
    protected $fillable=['acct_no', 'gl_no', 'description', 'gl_cat_no', 'gl_sub_cat_no', 'gl_type_no',
                        'acct_type', 'branch_cd', 'status', 'institution_id', 'branch_id', 'created_by', 'updated_by',
                        'created_on', 'updated_on'];
}
