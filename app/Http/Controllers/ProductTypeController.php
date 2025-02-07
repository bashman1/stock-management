<?php

namespace App\Http\Controllers;

use App\Models\ProductType;
use Illuminate\Http\Request;

class ProductTypeController extends Controller
{

    public function createProductTypes(Request $request){
        $userData = auth()->user();
        $type = new ProductType();
        $type->name = $request->name;
        $type->description = $request->description ;
        $type->institution_id = $userData->institution_id ;
        $type->branch_id = $userData->branch_id ;
        $type->user_id = $userData->id ;
        $type->status = $request->status ;
        $type->created_by = $userData->id ;
        $type->created_on = now() ;
        $type->save();
        return $this->genericResponse(true, "Product type created successfully", 201, $type, "createProductTypes", $request);
    }


    public function getProductTypes(){
        $userData = auth()->user();
        $types = ProductType::where(['status'=>'Active', 'institution_id'=>$userData->institution_id])->get();
        return $this->genericResponse(true, "Product type fetched successfully", 200, $types, "getProductTypes");
    }
}
