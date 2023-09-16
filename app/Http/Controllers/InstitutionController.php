<?php

namespace App\Http\Controllers;

use App\Models\Institution;
use App\Models\Branch;
use App\Models\InstitutionBank;
use App\Models\InstitutionContact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstitutionController extends Controller
{


    public function createInstitution(Request $request){

        DB::beginTransaction();
        try {
            $institution = new Institution();

            $institution->name=$request->name;
            $institution->institution_type_id=1;
            // $institution->institution_type_id=$request->type;
            $institution->start_date=$request->start_date;
            $institution->address=$request->address;
            // $institution->city_id=$request->city;
            $institution->city_id=1;
            $institution->street=$request->street;
            $institution->p_o_box=$request->p_o_box;
            $institution->description=$request->description;
            $institution->status=$request->status;
            $institution->created_on=now();
            // $institution->updated_on=$request->
            $institution->save();

            $branch = new Branch();

            $branch->name='Main';
            $branch->address=$request->address;
            $branch->city_id=1;
            $branch->street=$request->street;
            $branch->p_o_box=$request->p_o_box;
            $branch->institution_id=$institution->id;
            $branch->description=$request->description;
            $branch->status=$request->status;
            $branch->is_main=true;
            $branch->created_on=now();
            $branch->save();

            $bank = new InstitutionBank();
            $bank->bank_id=1;
            $bank->acct_name=$request->street;
            $bank->acct_number=1;
            $bank->status=$request->status;
            $bank->branch_id=$branch->id;
            $bank->institution_id=$institution->id;
            $bank->created_on=now();
            $bank->save();


            $contact = new InstitutionContact();
            $contact->name=$request->contact_name;
            $contact->phone_number=$request->contact_number;
            $contact->email=$request->contact_email;
            $contact->website=$request->contact_web;
            $contact->status=$request->status;
            $contact->institution_id=$institution->id;
            $contact->branch_id=$branch->id;
            $contact->created_on=now();
            $contact->save();
            DB::commit();
            return $this->genericResponse(true, "institution created successfully", 201, $institution);
        } catch (\Throwable $th) {
            DB::rollback();
            return $this->genericResponse(false, "institution creation Failed", 500, []);
        }
        
    }



    public function getInstitutions()
    {
        try {
            $institution = Institution::all();
            return $this->genericResponse(true, "institution created successfully", 201, $institution);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "institution creation Failed", 500, []);
        }

    }
}
