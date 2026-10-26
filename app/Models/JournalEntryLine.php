<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntryLine extends Model
{
    protected $fillable = [
        'journal_entry_id', 'acct_no', 'acct_type', 'dr_cr', 'amount', 'description',
        'institution_id', 'branch_id', 'created_by', 'created_on',
    ];
}
