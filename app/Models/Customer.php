<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable=['name', 'email', 'phone_number', 'address', 'status', 'description', 'user_id', 'branch_id',
        'customer_group_id', 'credit_limit', 'discount_percent', 'payment_term_id', 'tin',
        'institution_id', 'created_by', 'updated_by', 'created_on', 'updated_on'];

    public function group()
    {
        return $this->hasOne(CustomerGroup::class, 'id', 'customer_group_id');
    }

    public function paymentTerm()
    {
        return $this->hasOne(PaymentTerm::class, 'id', 'payment_term_id');
    }

    public function prices()
    {
        return $this->hasMany(CustomerPrice::class, 'customer_id', 'id');
    }
}
