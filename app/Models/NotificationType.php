<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationType extends Model
{
    //
    protected $fillable = [
        'name',
        'type', // e.g., 'email', 'sms', 'push'
        'status', // e.g., 'pending', 'sent', 'failed'
        'description',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on',
    ];
}
