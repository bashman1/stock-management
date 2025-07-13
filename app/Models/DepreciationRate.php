<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepreciationRate extends Model
{
    //

    public function duration()
    {
        return $this->belongsTo(Duration::class);
    }
}
