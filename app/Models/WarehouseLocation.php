<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id', 'parent_id', 'type', 'name', 'code', 'capacity',
        'institution_id', 'branch_id', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    public function children()
    {
        return $this->hasMany(WarehouseLocation::class, 'parent_id', 'id');
    }
}
