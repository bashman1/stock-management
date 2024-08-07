<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
   public function createCustomer(Request $request)
   {
       DB::beginTransaction();
       $userData = auth()->user();
       $customer = new Customer();
       $customer->name = $request->name ;
       $customer->email = $request->email ;
       $customer->phone_number = $request->phone_number ;
       $customer->address = $request->address ;
       $customer->status = $request->status ;
       $customer->description = $request->description ;
       $customer->user_id =  auth()->user()->id ;
       $customer->branch_id = $userData->branch_id ;
       $customer->institution_id = $userData->institution_id;
       $customer->created_by =  auth()->user()->id ;
       $customer->created_on = Carbon::now() ;
       $customer->save();
       DB::commit();
       return $this->genericResponse(true, "Customer created successfully", 201, $customer);
   }

   public function getCustomers(Request $request)
   {
       $userData = auth()->user();
       $customers = Customer::where('institution_id', $userData->institution_id)->get();
       return $this->genericResponse(true, "Customers", 200, $customers);
   }
}
