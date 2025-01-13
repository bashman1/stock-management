<?php

namespace App\Http\Controllers;

use App\Models\DefaultRole;
use App\Models\DefaultRolePermission;
use App\Models\Permissions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DefaultRoleController extends Controller
{
    public function createDefaultRole(Request $request){
        try {
            DB::beginTransaction();
            $defaultRole= DefaultRole::create([
                "name"=>$request->name,
                "status"=> $request->status,
                "description"=> $request->description,
                "created_by"=>auth()->user()->id,
                "created_on"=>Carbon::now(),
            ]);
            DB::commit();
            return $this->genericResponse(true, "Default role created successfully", 201, $defaultRole);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, $th);
        }
    }

    public function getDefaultRole(){
        try {
            $defaultRole = DefaultRole::where('status', 'Active')->get();
            return $this->genericResponse(true, "Default role fetched successfully", 200, $defaultRole);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, []);
        }
    }

    public function getDefaultRoleById($id){
        try {
            $defaultRole = DefaultRole::find($id);
            $defaultsRolePermission = DefaultRolePermission::where('role_id', $id)->get();
            $permissions = Permissions::where(["status" => "Active"])->get();
            return $this->genericResponse(true, "Role details", 200, [
                "role" => $defaultRole, "rolePermissions" => $defaultsRolePermission, "permissions" => $permissions
            ]);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, []);
        }
    }

    public function assignDefaultRolePermission(Request $request){
        try {
            $assignDefaultRolePermission = null;
            if($request->event){
                $assignDefaultRolePermission = new DefaultRolePermission();
                $assignDefaultRolePermission->role_id = $request->roleId;
                $assignDefaultRolePermission->permission_id = $request->permissionId;
                $assignDefaultRolePermission->created_by = auth()->user()->id;
                $assignDefaultRolePermission->created_on = now();
                $assignDefaultRolePermission->save();
            }else{
                $assignDefaultRolePermission =  DefaultRolePermission::where(["role_id" => $request->roleId, "permission_id"=> $request->permissionId])->first();
                $assignDefaultRolePermission->delete();
            }
            return $this->genericResponse(true, 201, "Permissions assigned successfully", $assignDefaultRolePermission);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, []);
        }
    }


}
