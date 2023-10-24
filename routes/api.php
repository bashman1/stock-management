<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\CityRefController;
use App\Http\Controllers\InstitutionTypeRefController;
use App\Http\Controllers\BankRefController;
use App\Http\Controllers\TempMemberController;
use \App\Http\Controllers\CollectionController;

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

Route::get('get-members', [MemberController::class,'getMembers']);
Route::get('get-role/{id}', [RoleController::class, 'getRoleById']);
Route::get('get-permissions', [RoleController::class, 'getPermissions']);
Route::post('assign-role-permission', [RoleController::class, 'assignRolePermission']);

Route::group(['middleware'=>["auth:api"]], function(){
    Route::post('crate-role', [RoleController::class, 'createRole']);
    Route::post('create-city', [CityRefController::class, 'createCity']);
    Route::get('get-city-county-id/{id}', [CityRefController::class, 'getCityByCountryId']);
    Route::post('create-institution-type', [InstitutionTypeRefController::class, 'InstitutionTypeRefController']);
    Route::get('get-institution-types', [InstitutionTypeRefController::class, 'getInstitutionTypes']);
    Route::post('create-bank-ref', [BankRefController::class, 'createBankRef']);
    Route::get('get-bank-ref', [BankRefController::class, 'getBanks']);
    Route::post('get-institution-branches', [InstitutionController::class, 'getBranchesByInstitutionId']);
    Route::post('get-institution-roles', [RoleController::class, 'getInstitutionRoles']);
    Route::post('get-role-type', [RoleController::class, 'getRolesByTypes']);
    Route::get('log-out', [UserController::class, 'logOut']);
    Route::post('create-member', [MemberController::class, 'createMember']);
    Route::post('upload-members', [TempMemberController::class, 'uploadMembers']);
    Route::post("get-upload-batch", [TempMemberController::class, "getMemberBatches"]);
    Route::post("get-batch-members", [TempMemberController::class, "getBatchMembers"]);
    Route::post("approve-batch-member", [MemberController::class, "approveBulkMembers"]);
    Route::post("get-members-by-institution", [MemberController::class, "getMembersByInstitution"]);
    Route::post("collect-deposit", [CollectionController::class, "fieldCollect"]);
    Route::get('get-officer-collection', [CollectionController::class, "getOfficerCollection"]);
});
