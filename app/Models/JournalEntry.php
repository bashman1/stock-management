<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = [
        'ref_no', 'journal_date', 'narration', 'total_amount', 'tran_id', 'status',
        'reversed_tran_id', 'reversed_on', 'reversed_by',
        'institution_id', 'branch_id', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    public function lines()
    {
        return $this->hasMany(JournalEntryLine::class, 'journal_entry_id');
    }
}
