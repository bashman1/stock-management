<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupplierRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_id', 'quality', 'delivery', 'price', 'communication', 'overall', 'comment',
        'rated_by', 'rating_date', 'institution_id', 'branch_id', 'status', 'created_on',
    ];

    protected $casts = [
        'quality' => 'float', 'delivery' => 'float', 'price' => 'float',
        'communication' => 'float', 'overall' => 'float',
    ];
}
