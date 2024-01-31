<?php

namespace App\Http\Controllers;

use App\Models\measurementUnit;
use Illuminate\Http\Request;

class MeasurementUnitController extends Controller
{

    public function createMeasurementUnit(Request $request){
        $userData = auth()->user();
        $unit = new measurementUnit();
        $unit->name = $request->name ;
        $unit->description = $request->description ;
        $unit->institution_id = $userData->institution_id ;
        $unit->branch_id = $userData->branch_id ;
        $unit->user_id = $userData->id ;
        $unit->status = $request->status ;
        $unit->created_by = $userData->id ;
        $unit->created_on = now();
        $unit->save();

        return $this->genericResponse(true, "Measurement unit created successfully", 201, $unit);
    }

    public function getMeasurementUnit(){
        $userData = auth()->user();
        $unit = measurementUnit::where(["status"=>"Active", "institution_id"=>$userData->institution_id])->get();
        return $this->genericResponse(true, "Measurement unit fetched successfully", 200, $unit);
    }

}
