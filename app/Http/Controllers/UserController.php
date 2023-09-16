<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LoginSession;
use Illuminate\Http\Request;

class UserController extends Controller
{
    
    public function createUser(Request $request){
        try {
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
            $user->role_id =  1;          
            $user->institution_id =1;          
            $user->branch_id = 1;         
            $user->created_by = 1;         
            $user->created_on = now();
            $user->save();
            return $this->genericResponse(true, "User created successfully", 201, $user);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "User creation  Failed", 500, []);
        }
    }


    public function login(Request $request){
        try {
            $login_data = $request->validate([
                "email" => "required",
                "password" => "required",
            ]);
    
            if (!auth()->attempt($login_data)) {
                return $this->genericResponse(false, "Invalid credentials", 404, ["login" => auth()->attempt($login_data)]);
            }
            $userData = auth()->user();

 
            ['user_id', 'token', 'status', 'locked',
    'logged_in_at', 'created_by', 'updated_by', 'created_on', 'updated_on'];



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

    
            return $this->genericResponse(true, "Logged in successfully", 200, ["token" => $token, "user_data" => $userData]);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "User creation  Failed", 500, $th);
        }
    }

    public function getUsers(){
        try {
            $users = User::all();
            return $this->genericResponse(true, "Users retrieved successfully", 200, $users);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "User creation  Failed", 500, []);
        }
    }
}
