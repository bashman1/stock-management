<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable =['acct_no', 'acct_type', 'contra_acct_no', 'contra_acct_type',
        'description', 'dr_cr_ind', 'tran_amount', 'reversal_flag', 'tran_date', 'tran_cd',
        'tran_id', 'status', 'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on'];
}
