<?php

namespace App\Http\Controllers;

use App\Models\CityRef;
use Illuminate\Http\Request;

class CityRefController extends Controller
{

    public function createCity(Request $request){
        $city = new CityRef();

        $city->country_id = $request->country_id;
        $city->name = $request->name;
        $city->code = $request->code;
        $city->status = $request->status;
        $city->created_on = now();
        $city->save();

        return $this->genericResponse(true, "City created successfully", 201, $city, "createCity", $request);
    }


    public function getCityByCountryId($country_id){
        $cities = CityRef::where('country_id', $country_id)->get();
        return $this->genericResponse(true, "Cities retrieved successfully", 200, $cities, "getCityByCountryId", $country_id);
    }
}
