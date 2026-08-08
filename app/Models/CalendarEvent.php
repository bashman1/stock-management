<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'start', 'end', 'all_day', 'color', 'category',
        'user_id', 'institution_id', 'branch_id', 'status',
        'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    protected $casts = [
        'all_day' => 'boolean',
    ];
}
