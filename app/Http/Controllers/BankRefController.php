<?php

namespace App\Http\Controllers;

use App\Models\BankRef;
use Illuminate\Http\Request;

class BankRefController extends Controller
{

    public function createBankRef(Request $request){
        $bank = new BankRef();
        $bank->name = $request->name;
        $bank->code = $request->code;
        $bank->status = $request->status;
        // $bank->created_by = $request->created_by ;
        $bank->created_on = now();
        $bank->save();
        return $this->genericResponse(true, "Bank created successfully", 201, $bank);
    }



    public function getBanks(){
        $banks =  BankRef::all();
        return $this->genericResponse(true, "City created successfully", 200, $banks);
    }
}
