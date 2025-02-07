<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;
use Illuminate\Http\Request;

class ManufacturerController extends Controller
{

    public function createManufacturer(Request $request){

        $userData = auth()->user();
        $manufacturer = new Manufacturer();
        $manufacturer->name = $request->name ;
        $manufacturer->website = $request->website ;
        $manufacturer->email = $request->email ;
        $manufacturer->phone_number = $request->phone_number ;
        $manufacturer->country_id = $request->country_id ;
        $manufacturer->description = $request->description ;
        $manufacturer->status = $request->status;
        $manufacturer->institution_id = $userData->institution_id ;
        $manufacturer->branch_id = $userData->branch_id ;
        $manufacturer->user_id = $userData->id;
        $manufacturer->created_by = $userData->id;
        $manufacturer->created_on = now();
        $manufacturer->save();

        return $this->genericResponse(true, "Manufacturer created successfully", 201, $manufacturer, "createManufacturer", $request);
    }


    public function getManufacturers(){
        $userData = auth()->user();
        $manufacturers = Manufacturer::where(["status"=>"Active", "institution_id"=>$userData->institution_id])->get();
        return $this->genericResponse(true, "Manufacturer fetched successfully", 200, $manufacturers, "getManufacturers", []);
    }
}
