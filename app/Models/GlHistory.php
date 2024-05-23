<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlHistory extends Model
{
    use HasFactory;
    protected $fillable =['acct_no', 'dr_cr_ind', 'tran_amount', 'reversal_flag', 'description',
                        'transaction_date', 'contra_acct_no', 'contra_acct_type', 'tran_type', 
                        'tran_id', 'tran_cd', 'status', 'institution_id', 'branch_id', 'created_by',
                        'updated_by', 'created_on', 'updated_on'];
}
