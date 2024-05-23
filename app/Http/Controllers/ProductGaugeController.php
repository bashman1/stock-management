<?php

namespace App\Http\Controllers;

use App\Models\ProductGauge;
use Illuminate\Http\Request;

class ProductGaugeController extends Controller
{

    public function createGauge(Request $request){
        $userData = auth()->user();
        $gauge = new ProductGauge();
        $gauge->name = $request->name ;
        $gauge->description = $request->description ;
        $gauge->institution_id = $userData->institution_id ;
        $gauge->branch_id = $userData->branch_id ;
        $gauge->user_id = $userData->id ;
        $gauge->status = $request->status ;
        $gauge->created_by = $userData->id ;
        $gauge->created_on =now() ;
        $gauge->save() ;

        return $this->genericResponse(true, "Product gauge created successfully", 201, $gauge);
    }

    public function getGauges(){
        $userData = auth()->user();
        $gauge = ProductGauge::where(["status"=>"Active", "institution_id"=>$userData->institution_id])->get();
        return $this->genericResponse(true, "Product gauge fetched successfully", 200, $gauge);

    }
}
