<?php

namespace App\Http\Controllers;

use App\Models\SavingAccountProduct;
use Illuminate\Http\Request;
use App\Services\SavingAccountProductService;

class SavingAccountProductController extends Controller
{

    protected $savingsProduct;

    public function __construct(SavingAccountProductService $savingsProduct){
        $this->savingsProduct = $savingsProduct;
    }

    public function createSavingsProduct(Request $request){
        try {

            $product=$this->savingsProduct->createSavingsProduct($request);
            // $product = new SavingAccountProduct();
            // $product->name = $request->name ;
            // $product->description = $request->description ;
            // $product->balance = $request->balance;
            // $product->min_balance = $request->min_balance ;
            // $product->withdraw_allowed = $request->withdraw_allowed;
            // $product->overdraw_allowed = $request->overdraw_allowed ;
            // $product->currency = $request->currency ;
            // $product->institution_id = $request->institution_id ;
            // $product->branch_id = $request->branch_id ;
            // $product->user_id = $request->user_id ;
            // $product->status = $request->status ;
            // $product->created_by = $request->created_by ;
            // $product->created_on = $request->created_on ;
            // $product->save();
            return $this->genericResponse(true, "Saving account created successfully", 201, $product, "createSavingsProduct", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "Saving account created successfully", 400, $th, "createSavingsProduct", $request);
        }
    }

}
