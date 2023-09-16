<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
   

    public function createRole(Request $request){
        $role = new Role();
        try {
            $userData = auth()->user();
            $role->name = $request->name;
            $role->type = $request->type;
            $role->institution_id = $request->institution_id;
            $role->status = $request->status;
            $role->description = $request->description;
            $role->created_by = 1;//$userData->id;
            $role->created_on =now();
            $role->save();
            return $this->genericResponse(true, "Role crated successfully", 201, $role);
        } catch (\Exception $th) {
            return $this->genericResponse(false, "Role creation  Failed", 500, $th);
        }
    }



    public function getAllRoles(){
        try {
            $roles =  Role::all();
            return $this->genericResponse(true, "Roles retrieved successfully", 200, $roles);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "Role retrieval  Failed", 500, $th);
        }
    }
}
