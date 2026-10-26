<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'branch_id', 'institution_id', 'manager_id', 'capacity', 'is_default',
        'address', 'status', 'created_by', 'updated_by', 'created_on', 'updated_on',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'capacity' => 'float',
    ];

    public function manager()
    {
        return $this->hasOne(User::class, 'id', 'manager_id');
    }

    public function branch()
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id');
    }

    public function locations()
    {
        return $this->hasMany(WarehouseLocation::class, 'warehouse_id', 'id');
    }

    public function stocks()
    {
        return $this->hasMany(WarehouseStock::class, 'warehouse_id', 'id');
    }
}
