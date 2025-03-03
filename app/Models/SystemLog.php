<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    //

    protected $fillable = [
        'action',
        'ip',
        'request',
        'response',
        'status',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on',
        'http_code',
        'return_message',
        'return_status',
        'user_id',
        'institution_id',
        'branch_id',
        'purpose',
        'description',
        'country',
        'city',
        'asn',
        'region',
        'latitude',
        'longitude',
        'postal_code',
        'isp',
        'time_zone',
        'uuid',
    ];

    protected $casts = [
        'request' => 'array',
        'response' => 'array',
    ];
}
