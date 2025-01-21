<?php

namespace App\Http\Controllers;

use App\Models\CountryRef;
use Illuminate\Http\Request;

class CountryRefController extends Controller
{

    public function getCountries(){
        $countries = CountryRef::where("status", "Active")->get();
        return $this->genericResponse(true, "Countries fetched Successfully", 200, $countries, "getCountries", []);
    }
}
