<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //
    protected $fillable = [
        'notification_type_id',
        'category',
        'user_id',
        'customer_id',
        'member_id',
        'supplier_id',
        'manufacturer_id',
        'name',
        'email',
        'phone',
        'daily',
        'weekly',
        'monthly',
        'quarterly',
        'yearly',
        'description',
        'institution_id',
        'branch_id',
        'created_by',
        'updated_by',
        'created_on',
        'updated_on',
    ];
}
