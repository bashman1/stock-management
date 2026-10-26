<?php

namespace App\Http\Controllers;

use App\Models\measurementUnit;
use App\Models\EfrisMeasurementUnits;
use Illuminate\Http\Request;

class MeasurementUnitController extends Controller
{

    public function createMeasurementUnit(Request $request){
        $userData = $this->getUserLogin();
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

        return $this->genericResponse(true, "Measurement unit created successfully", 201, $unit, "createMeasurementUnit", $request);
    }

    public function getMeasurementUnit(){
        $userData = $this->getUserLogin();
        $institutionData = $this->getUserInstitution();
        $unit = [];
        if ($institutionData->efris_enabled) {
            $unit = $this->getEfrisMeasurementUnits(new Request());
        } else {
            $unit = measurementUnit::where(["status"=>"Active", "institution_id"=>$userData->institution_id])->get();
        }

        return $this->genericResponse(true, "Measurement unit fetched successfully", 200, $unit, "getMeasurementUnit", []);
    }

    public function getEfrisMeasurementUnits(Request $request){
        try {
            $name = trim((string) $request->input('name', ''));
            $units = EfrisMeasurementUnits::query()
            ->where('status', 'Active')
            ->when($name !== '', function ($q) use ($name) {
                $q->where('name', 'ILIKE', "%{$name}%");
            })
            ->orderBy('name')
            ->limit(1000)
            ->get();
            return $units;

        } catch (\Throwable $th) {
            throw $th;
        }
    }




}
