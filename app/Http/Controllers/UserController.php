<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginSession;
use Illuminate\Http\Request;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{

    public function createUser(Request $request){
        // try {
            $user = new User();
            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->other_name = $request->other_name;
            $user->email = $request->email;
            $user->password = bcrypt($request->password);
            $user->phone_number = $request->phone_number;
            $user->gender = $request->gender;
            $user->date_of_birth = $request->date_of_birth;
            $user->address = $request->address;
            $user->city_id = $request->city_id;
            $user->status = $request->status;
            $user->street = $request->street;
            $user->p_o_box = $request->p_o_box;
            $user->description = $request->description;
            $user->role_id =  $request->role_id;
            $user->user_type = $request->user_type;
            $user->user_category =  $request->user_category;
            $user->institution_id = $request->institution_id;
            $user->branch_id =  $request->institution_id;
            $user->created_by = 1;
            $user->created_on = now();
            $user->save();
            return $this->genericResponse(true, "User created successfully", 201, $user);
        // } catch (\Throwable $th) {
        //     return $this->genericResponse(false, "User creation  Failed", 500, []);
        // }
    }


    public function login(Request $request){
        // try {
            $login_data = $request->validate([
                "email" => "required",
                "password" => "required",
            ]);

            if (!auth()->attempt($login_data)) {
                return $this->genericResponse(false, "Invalid credentials", 404, ["login" => auth()->attempt($login_data)]);
            }
            $userData = auth()->user();

            $token = auth()->user()->createToken("auth_token");

            $loggedInSession = LoginSession::where('user_id', $userData->id)->first();
            if(!isset($loggedInSession->id)){
                $loggedInSession = new LoginSession();
                $loggedInSession->user_id = $userData->id;
                $loggedInSession->created_by = $userData->id;
                $loggedInSession->created_on = now();
                $loggedInSession->status = "Logged In Successfully";
            }
            $loggedInSession->logged_in_at=now();
            $loggedInSession->token=now();
            $loggedInSession->save();

            $role = Role::find($userData->role_id);
            $permissions = DB::select("SELECT R.id, R.role_id, R.permission_id, P.name, P.description, P.is_admin, P.status
            FROM role_permissions R INNER JOIN permissions P ON R.permission_id = P.id
            WHERE R.role_id= " . $userData->role_id . " ");

            $userData->role = $role;
            $userData->permissions = $permissions;

            return $this->genericResponse(true, "Logged in successfully", 200, ["token" => $token, "user_data" => $userData]);
        // } catch (\Exception $e) {
        //     return $this->genericResponse(false, "User creation  Failed", 500, $e->getMessage());
        // }
    }

    public function getUsers(){
        try {
            $users = User::orderBy('id', 'desc')->get();
            return $this->genericResponse(true, "Users retrieved successfully", 200, $users);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "User creation  Failed", 500, []);
        }
    }

    public function logOut(){
        $user = auth()->user()->token();
        $user->revoke();
        return $this->genericResponse(true, "Users logged out successfully", 200, []);
    }
}
