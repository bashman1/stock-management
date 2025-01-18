<?php

namespace App\Services;
use App\Models\SavingAccountProduct;


class SavingAccountProductService{

    public function createSavingsProduct($request) {
        try {
            $product = new SavingAccountProduct();
            $product->name = $request->name ;
            $product->description = $request->description ;
            $product->balance = $request->balance;
            $product->min_balance = $request->min_balance ;
            $product->withdraw_allowed = $request->withdraw_allowed;
            $product->overdraw_allowed = $request->overdraw_allowed ;
            $product->currency = $request->currency ;
            $product->is_default=$request->is_default;
            $product->institution_id = $request->institution_id ;
            $product->branch_id = $request->branch_id ;
            $product->user_id = $request->user_id ;
            $product->status = $request->status ;
            $product->created_by = 1 ;
            $product->created_on = now();
            $product->save();
            return $product;
            // return $this->genericResponse(true, "Saving account created successfully", 201, $product);
        } catch (\Throwable $th) {
            throw new $th;
            // return $this->genericResponse(false, "Saving account created successfully", 400, $th);
        }
    }

}

