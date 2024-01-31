<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{

    public function createSupplier(Request $request){
        $supplier = new Supplier();
        $userData = auth()->user();
        $supplier->name = $request->name ;
        $supplier->website = $request->website ;
        $supplier->email = $request->email ;
        $supplier->phone_number = $request->phone_number;
        $supplier->country_id = $request->country_id ;
        $supplier->institution_id = $userData->institution_id ;
        $supplier->branch_id = $userData->branch_id ;
        $supplier->user_id = $userData->id ;
        $supplier->description = $request->description ;
        $supplier->status = $request->status ;
        $supplier->created_by = $userData->id ;
        $supplier->created_on = now() ;
        $supplier->save();

        return $this->genericResponse(true, "Supplier created successfully", 201, $supplier);
    }

    public function getSuppliers(){
        $userData = auth()->user();
        $suppliers= Supplier::where(["status"=>"Active", "institution_id"=>$userData->institution_id])->get();
        return $this->genericResponse(true, "Supplier fetched successfully", 200, $suppliers);
    }
}
