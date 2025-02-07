<?php

namespace App\Http\Controllers;

use App\Models\InstitutionTypeRef;
use Illuminate\Http\Request;

class InstitutionTypeRefController extends Controller
{

    public function createInstitutionType(Request $request){
        $type = new InstitutionTypeRef();
        $type->name = $request->name;
        $type->status = $request->status;
        // $type->created_by = $request->created_by;
        $type-> created_on= now();
        $type->save();
        return $this->genericResponse(true, "Institution Type Created successfully", 201, $type, "createInstitutionType", $request);
    }


    public function getInstitutionTypes(){
        $types = InstitutionTypeRef::all();
        return $this->genericResponse(true, "Institution Type retrieved successfully", 200, $types, "getInstitutionTypes", []);
    }

}
