<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\RolePermission;
use App\Models\Permissions;

class RoleController extends Controller
{


    public function createRole(Request $request){
        $role = new Role();
        // try {
            $userData = auth()->user();
            $role->name = $request->name;
            $role->type = $request->type;
            $role->institution_id = $request->institution_id;
            $role->status = $request->status;
            $role->role_type=$request->role_type;
            $role->description = $request->description;
            $role->created_by = $userData->id;
            $role->created_on =now();
            $role->save();
            return $this->genericResponse(true, "Role crated successfully", 201, $role, "createRole", $request);
        // } catch (\Exception $th) {
            // return $this->genericResponse(false, "Role creation  Failed", 500, $th);
        // }
    }


    public function getAllRoles(){
        try {
            $roles =  Role::orderBy("id", "desc")->get();
            return $this->genericResponse(true, "Roles retrieved successfully", 200, $roles);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "Role retrieval  Failed", 500, $th, "getAllRoles", []);
        }
    }


    public function getRoleById($id){
        // try {
        //    $role = Role::find($id);
        //    return $this->genericResponse(true, "Roles retrieved successfully", 200, $role);
        // } catch (\Throwable $th) {
        //     return $this->genericResponse(false, "Role retrieval  Failed", 500, $th);
        // }
        $role = Role::find($id);
        $rolePermissions = RolePermission::where(["role_id" => $id])->get();
        $permissions = Permissions::where(["status" => "Active"])->get();
        return $this->genericResponse(true, "Role details", 200, [
            "role" => $role, "rolePermissions" => $rolePermissions, "permissions" => $permissions
        ], "getRoleById", $id);
    }


    public function getPermissions(){
        // try {
            $permissions= Permissions::where('status', 'Active')->orderBy('id', 'asc')->get();
            return $this->genericResponse(true, "Roles retrieved successfully", 200, $permissions, "getPermissions", []);
        // } catch (\Throwable $th) {
        //     return $this->genericResponse(false, "Role retrieval  Failed", 500, $th);
        // }
    }


    public function assignRolePermission(Request $request){
        try {
            $assignRolePermissions = null;
            if($request->event){
                $assignRolePermissions = new RolePermission();
                $assignRolePermissions->role_id = $request->roleId;
                $assignRolePermissions->permission_id = $request->permissionId;
                // $assignRolePermissions->created_by = $user->id;
                $assignRolePermissions->created_on = now();
                $assignRolePermissions->save();
            }else{
                $assignRolePermissions =  RolePermission::where(["role_id" => $request->roleId, "permission_id"=> $request->permissionId])->first();
                $assignRolePermissions->delete();
            }
            return $this->genericResponse(true, 201, "Permissions assigned successfully", $assignRolePermissions, "assignRolePermission", $request);

        } catch (\Throwable $th) {
            return $this->genericResponse(false, "Role retrieval  Failed", 500, $th, "assignRolePermission", $request);
        }
    }

    public function getInstitutionRoles(Request $request){
        try {
            $roles =  Role::where(['institution_id'=>$request->institutionId, 'status'=>$request->status])->get();
            return $this->genericResponse(true, "Roles retrieved successfully", 200, $roles, "getInstitutionRoles", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "Role retrieval  Failed", 500, $th, "getInstitutionRoles", $request);
        }
    }


    public function getRolesByTypes(Request $request){

        try {
            $roles =  Role::where(['role_type'=>$request->roleType, 'status'=>$request->status])->get();
            return $this->genericResponse(true, "Roles retrieved successfully", 200, $roles, "getRolesByTypes", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "Role retrieval  Failed", 500, $th, "getRolesByTypes", $request);
        }

    }
}
