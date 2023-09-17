<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MemberController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::post('create-institution', [InstitutionController::class, 'createInstitution']);
Route::post('create-user', [UserController::class, 'createUser']);
Route::post('user-login', [UserController::class, 'login']);

Route::get('get-roles', [RoleController::class, 'getAllRoles']);
Route::get('get-institutions', [InstitutionController::class, 'getInstitutions']);
Route::get('get-users', [UserController::class, 'getUsers']);
Route::post('create-member', [MemberController::class, 'createMember']);
Route::get('get-members', [MemberController::class,'getMembers']);
Route::get('get-role/{id}', [RoleController::class, 'getRoleById']);
Route::get('get-permissions', [RoleController::class, 'getPermissions']);
Route::post('assign-role-permission', [RoleController::class, 'assignRolePermission']);

Route::group(['middleware'=>["auth:api"]], function(){
    Route::post('crate-role', [RoleController::class, 'createRole']);
});
