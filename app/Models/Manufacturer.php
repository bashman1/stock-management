<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    use HasFactory;

    protected $fillable=["name", "website", "email", "phone_number", "country_id",
        "address", "city_id", "payment_term_id", "credit_limit", "rating", "tin",
        "institution_id", "branch_id", "user_id", "description", "status", "created_by",
        "updated_by", "created_on", "updated_on"];

    public function paymentTerm()
    {
        return $this->hasOne(PaymentTerm::class, 'id', 'payment_term_id');
    }

    public function contacts()
    {
        return $this->hasMany(ManufacturerContact::class, 'manufacturer_id', 'id');
    }

    public function ratings()
    {
        return $this->hasMany(ManufacturerRating::class, 'manufacturer_id', 'id');
    }

    public function contracts()
    {
        return $this->hasMany(ManufacturerContract::class, 'manufacturer_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany(ManufacturerDocument::class, 'manufacturer_id', 'id');
    }
}
