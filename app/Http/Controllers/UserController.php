<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Institution;
use App\Models\User;
use App\Models\LoginSession;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\UserBranch;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class UserController extends Controller
{


    public function createUser(Request $request){
        try {
            $user = $this->newUser($request);
            return $this->genericResponse(true, "User created successfully", 201, $user, "createUser", $request);
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
                return $this->genericResponse(false, "Invalid credentials", 404, ["login" => auth()->attempt($login_data)], "login", $request->all());
            }
            $userData = auth()->user();

            if ($userData->status != 'Active'){
                return $this->genericResponse(false, "User account not activated", 404, ["login" => auth()->attempt($login_data)], "login", $request->all());
            }

            $license= null;

            if (isset($userData->institution_id)){
                $institution = Institution::find($userData->institution_id);
                $branch = Branch::find($userData->branch_id);

                if ($institution->status != 'Active'){
                    return $this->genericResponse(false, "Business not activated", 404, ["login" => auth()->attempt($login_data)], "login", $request->all());
                }

                $userData->institution_name=$institution->name;
                $userData->branch_name=$branch->name;
                $userData->institution_logo=$institution->image;


                $license = $this->getLicenseDetails($institution->id);
                if (isset($license[0]->id)){
                    if (Carbon::parse($license[0]->end_date)->isPast()) {
                        // End date is before now (expired)
                        return $this->genericResponse(false, "Business license expired", 404, [], "login", $request);
                    }
                    $license = $license[0];
                }
            }else{
                $userData->institution_name=null;
                $userData->branch_name=null;
            }

            $role = Role::find($userData->role_id);
            $userData->role_name=$role->name;

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

            return $this->genericResponse(true, "Logged in successfully", 200, ["token" => $token, "user_data" => $userData, 'license'=>$license], "login", $request);
        } catch (\Exception $e) {
            return $this->genericResponse(false, "User creation  Failed", 500, $e->getMessage());
        }
    }

    public function getUsers(){
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();

            $queryString = "SELECT U.id,U.first_name, U.last_name,U.other_name, U.phone_number, U.gender, U.date_of_birth, U.address, U.city_id, U.email,
            U.status, U.user_type, U.user_category, U.street, U.p_o_box, U.description, U.role_id, U.institution_id, U.branch_id, U.created_at,
            U.created_on,U.updated_at, C.name AS city, R.name AS role, I.name AS institution, B.name AS branch,
            CONCAT(V.first_name,' ', V.last_name,' ', V.other_name) as created_by
            FROM users U LEFT JOIN city_refs C ON C.id =U.city_id
            LEFT JOIN roles R ON R.id = U.role_id
            LEFT JOIN institutions I ON I.id = U.institution_id
            LEFT JOIN branches B ON B.id = U.branch_id
            LEFT JOIN users V ON V.id = U.created_by  ";
            if($isNotAdmin){
                    $queryString.=" WHERE U.institution_id = $userData->institution_id AND U.branch_id=$userData->branch_id ";
            }
            $queryString.=" ORDER BY U.id DESC ";
            $users = DB::select($queryString);
            return $this->genericResponse(true, "Users retrieved successfully", 200, $users, "getUsers", []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "User creation  Failed", 500, [], "getUsers", []);
        }
    }


    /**
     * Server-side paginated + filtered user list for the Users table (lazy load).
     * Filters: search (name/email/phone/institution/role), status, created date range.
     */
    public function getUsersList(Request $request){
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();

            $select = "SELECT U.id, U.first_name, U.last_name, U.other_name, U.phone_number, U.gender, U.email,
                U.status, U.role_id, U.institution_id, U.branch_id, U.created_at,
                R.name AS role, I.name AS institution, B.name AS branch,
                CONCAT(V.first_name,' ', V.last_name,' ', V.other_name) as created_by ";
            $from = " FROM users U
                LEFT JOIN roles R ON R.id = U.role_id
                LEFT JOIN institutions I ON I.id = U.institution_id
                LEFT JOIN branches B ON B.id = U.branch_id
                LEFT JOIN users V ON V.id = U.created_by ";

            $where = " WHERE 1=1 ";
            if ($isNotAdmin) {
                $where .= " AND U.institution_id = " . (int) $userData->institution_id . " AND U.branch_id = " . (int) $userData->branch_id . " ";
            }
            if (!empty(trim($request->search ?? ''))) {
                $s = str_replace("'", "", trim($request->search));
                $where .= " AND (U.first_name ILIKE '%$s%' OR U.last_name ILIKE '%$s%' OR U.other_name ILIKE '%$s%'
                    OR U.email ILIKE '%$s%' OR U.phone_number ILIKE '%$s%' OR I.name ILIKE '%$s%' OR R.name ILIKE '%$s%') ";
            }
            if (!empty($request->status)) {
                $st = str_replace("'", "", $request->status);
                $where .= " AND U.status = '$st' ";
            }
            if (!empty($request->startDate)) {
                $sd = date('Y-m-d', strtotime($request->startDate));
                $where .= " AND U.created_at::date >= '$sd' ";
            }
            if (!empty($request->endDate)) {
                $ed = date('Y-m-d', strtotime($request->endDate));
                $where .= " AND U.created_at::date <= '$ed' ";
            }

            $queryString = $select . $from . $where . " ORDER BY U.id DESC ";
            $totalSql = " SELECT COUNT(U.id) AS total " . $from . $where;

            $users = $this->paginate($request->rowPerPage, $totalSql, $queryString);
            return $this->genericResponse(true, "Users retrieved successfully", 200, $users, "getUsersList", []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "Users could not be retrieved", 500, [], "getUsersList", []);
        }
    }


    public function getUserDetails(Request $request){
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();

            $queryString = "SELECT U.id,U.first_name, U.last_name,U.other_name, U.phone_number, U.gender, U.date_of_birth, U.address, U.city_id, U.email, U.image,
            U.status, U.user_type, U.user_category, U.street, U.p_o_box, U.description, U.role_id, U.institution_id, U.branch_id, U.created_at,
            U.created_on,U.updated_at, C.name AS city, R.name AS role, I.name AS institution, B.name AS branch,
            CONCAT(V.first_name,' ', V.last_name,' ', V.other_name) as created_by
            FROM users U LEFT JOIN city_refs C ON C.id =U.city_id
            INNER JOIN roles R ON R.id = U.role_id
            INNER JOIN institutions I ON I.id = U.institution_id
            INNER JOIN branches B ON B.id = U.branch_id
            LEFT JOIN users V ON V.id = U.created_by
            WHERE U.id = $request->userId ";
            if($isNotAdmin){
                    $queryString.=" AND U.institution_id = $userData->institution_id ";
            }
            $queryString.=" ORDER BY U.id DESC ";
            $users = DB::select($queryString);
            return $this->genericResponse(true, "Users retrieved successfully", 200, $users, "getUserDetails", $request);

        } catch (\Throwable $th) {
            return $this->genericResponse(false, "User details could not be retrieved", 500, [], "getUserDetails", $request);
        }
    }

    public function logOut(){
        $user = auth()->user()->token();
        $user->revoke();
        return $this->genericResponse(true, "Users logged out successfully", 200, [], "logOut", []);
    }


    public function assignBranchesToUsers(Request $request){
        try {
            $userData = $this->getUserLogin();
            DB::beginTransaction();
            $clearExisting = UserBranch::where(["user_id"=> $request->user, "status"=> "Active"])->get();

            foreach ($clearExisting as $key => $value) {
                $value->update([
                    "status" => "Deleted",
                    "updated_by" => $userData->id, // Optional: if you have an `updated_by` field
                    "updated_on" => Carbon::now(), // Optional: if you want to track the update time
                ]);
            }

            foreach ($request->branches as $key => $value) {
                $existing = UserBranch::where(["user_id"=> $request->user, "branch_id"=> $value["id"]])->first();
                if(isset($existing)){
                    $existing->update([
                        "status" => "Active",
                        "updated_by" => $userData->id, // Optional: if you have an `updated_by` field
                        "updated_on" => Carbon::now(), // Optional: if you want to track the update time
                    ]);
                }else{
                    UserBranch::create([
                        "user_id"=> $request->user,
                        "branch_id"=> $value["id"],
                        "status"=>"Active",
                        "institution_id"=>$value["institution_id"],
                        "created_by"=>$userData->id,
                        "created_on"=>Carbon::now(),
                    ]);
                }
            }
            DB::commit();
            return $this->genericResponse(true, "Users branch assigned successfully", 200, [], "assignBranchesToUsers", $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false,  $th->getMessage(), 500,  $th, "assignBranchesToUsers", $request);
        }
    }

    public function getUserBranches(Request $request){
        try {
            $userBranches = DB::select("SELECT U.*, B.name FROM user_branches U INNER JOIN branches B ON U.branch_id = B.id WHERE U.user_id = $request->user_id AND  U.status = '$request->status' ");
           return $this->genericResponse(true, "Users branch fetched successfully", 200, $userBranches, "getUserBranches", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false,  $th->getMessage(), 500,  $th, "getUserBranches", $request);
        }
    }

    public function switchBranch(Request $request){
        try {
            DB::beginTransaction();
                $user = User::find($request->userId);
                if(isset($user)){
                    if($user->original_branch_id){
                        $user->branch_id=$request->branchId;
                    }else{
                        $user->original_branch_id=$user->branch_id;
                        $user->branch_id=$request->branchId;
                    }
                    $user->update();

                    if (isset($user->institution_id)){
                        $institution = Institution::find($user->institution_id);
                        $branch = Branch::find($user->branch_id);

                        $user->institution_name=$institution->name;
                        $user->branch_name=$branch->name;
                    }else{
                        $user->institution_name=null;
                        $user->branch_name=null;
                    }

                    $role = Role::find($user->role_id);
                    $user->role_name=$role->name;

                    $permissions = DB::select("SELECT R.id, R.role_id, R.permission_id, P.name, P.description, P.is_admin, P.status
                    FROM role_permissions R INNER JOIN permissions P ON R.permission_id = P.id
                    WHERE R.role_id= " . $user->role_id . " ");

                    $user->role = $role;
                    $user->permissions = $permissions;
                }else{
                    return $this->genericResponse(false,  "User not found", 404, $user, "switchBranch", $request);
                }
            DB::commit();
            return $this->genericResponse(true, "Branch switched successfully.", 200,  $user, "switchBranch", $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false,  $th->getMessage(), 500,  $th, "switchBranch", $request);
        }
    }

    public function resetPassword(Request $request) {
        try {
            DB::beginTransaction();

            // Find the user by userId
            $user = User::find($request->userId);
            if (!$user) {
                return $this->genericResponse(false, "User not found", 404, null, "resetPassword", $request);
            }

            // Ensure new password and confirm password match
            if ($request->newPassword !== $request->confirmPassword) {
                return $this->genericResponse(false, "New password and confirm password do not match", 401, null , "resetPassword", $request);
            }

            // Update the user's password
            $user->password = bcrypt($request->newPassword);
            $user->reset_required = false;
            $user->save();

            DB::commit();
            return $this->genericResponse(true, "Password reset successfully.", 200, $user,  "resetPassword", $request);

        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "resetPassword", $request);
        }
    }


    // passwsword reset methods

       public function requestForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    public function resetForm($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }


    public function updateUserStatus(Request $request)
    {
        // try {
            $user = User::find($request->userId);
            // if (!$user) {
            //     return $this->genericResponse(false, "User not found", 404, null, "updateUserStatus", $request);
            // }

            $user->status = $request->status;
            $user->save();

            return $this->genericResponse(true, "User status updated successfully", 200, $user, "updateUserStatus", $request);
        // } catch (\Throwable $th) {
        //     return $this->genericResponse(false, "Failed to update user status: " . $th->getMessage(), 500, [], "updateUserStatus", $request);
        // }
    }


}
