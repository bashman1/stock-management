<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutGoingMail extends Model
{
    use HasFactory;

    protected $fillable = ["subject", "body", "to", "cc", "bcc", "sent", "sent_at",
     "status", "failure_reason", "failed_at", "retry_after", "created_by", "updated_by",
    "created_on", "updated_on", "failure_count","priority", "attachment", "attachment_name", "type", "has_attachment"];

    protected $casts = [
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'attachment' => 'array',
    ];

}
