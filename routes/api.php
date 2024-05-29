<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstitutionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\CityRefController;
use App\Http\Controllers\InstitutionTypeRefController;
use App\Http\Controllers\BankRefController;
use App\Http\Controllers\TempMemberController;
use \App\Http\Controllers\CollectionController;
use App\Http\Controllers\ComissionController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\CountryRefController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\MeasurementUnitController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SavingAccountProductController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductTypeController;
use App\Http\Controllers\ProductGaugeController;
use App\Http\Controllers\GLBalanceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MtnPaymentsController;
use App\Http\Controllers\GlAccountsController;
use App\Http\Controllers\ReportController;

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


// Route::post('create-institution', [InstitutionController::class, 'createInstitution']);
// Route::post('create-user', [UserController::class, 'createUser']);
Route::post('user-login', [UserController::class, 'login']);
// Route::get('get-roles', [RoleController::class, 'getAllRoles']);
// Route::get('get-institutions', [InstitutionController::class, 'getInstitutions']);
// Route::get('get-users', [UserController::class, 'getUsers']);
// Route::get('get-members', [MemberController::class,'getMembers']);
// Route::get('get-role/{id}', [RoleController::class, 'getRoleById']);
// Route::get('get-permissions', [RoleController::class, 'getPermissions']);
// Route::post('assign-role-permission', [RoleController::class, 'assignRolePermission']);
Route::get('create-branch-codes', [InstitutionController::class, "generateMissingCodesForBranches"]);
Route::get('create-gl-accts', [InstitutionController::class, "generateMissingLedgers"]);
Route::get('set-cntrl-parameter', [InstitutionController::class, "generateMissingCntrlParameter"]);


Route::get('create-gl-bk', [GLBalanceController::class, "generateGlAcctBks"]);
Route::post('test-momo-api', [MtnPaymentsController::class, "getMOMOAuth"]);
Route::post('test-chart-accounts', [GlAccountsController::class, "getChartOfAccounts"]);


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
    Route::get('get-transaction-receipt/{id}', [CollectionController::class, "getReceiptData"]);
    Route::get('get-pending-transactions/{status}', [CollectionController::class, "getPendingTransactions"]);
    Route::post('approve-transactions', [CollectionController::class, "approveTransactions"]);
    Route::get('get-collected-transactions', [CollectionController::class, "getApprovedTransactions"]);
    Route::post('create-savings-product', [SavingAccountProductController::class, "createSavingsProduct"]);
    Route::post('create-user', [UserController::class, 'createUser']);
    Route::post('create-institution', [InstitutionController::class, 'createInstitution']);
    Route::get('get-roles', [RoleController::class, 'getAllRoles']);
    Route::get('get-institutions', [InstitutionController::class, 'getInstitutions']);
    Route::get('get-users', [UserController::class, 'getUsers']);
    Route::get('get-members', [MemberController::class,'getMembers']);
    Route::get('get-role/{id}', [RoleController::class, 'getRoleById']);
    Route::get('get-permissions', [RoleController::class, 'getPermissions']);
    Route::post('assign-role-permission', [RoleController::class, 'assignRolePermission']);
    Route::get('get-commissions', [ComissionController::class, "getCommissionsEarned"]);
    Route::post('create-product-category', [ProductCategoryController::class, "createProductCategory"]);
    Route::get('get-product-categories', [ProductCategoryController::class, "getProductCategories"]);
    Route::post('create-product-sub-category', [ProductCategoryController::class, "createProductSubCategory"]);
    Route::get('get-product-sub-categories', [ProductCategoryController::class, "getProductSubCategory"]);
    Route::get('get-countries', [CountryRefController::class, "getCountries"]);

    Route::post('create-manufacturer', [ManufacturerController::class, "createManufacturer"]);
    Route::get('get-manufacturers', [ManufacturerController::class, "getManufacturers"]);

    Route::post('create-supplier', [SupplierController::class, "createSupplier"]);
    Route::get('get-suppliers', [SupplierController::class, "getSuppliers"]);

    Route::post('create-measurement-unit', [MeasurementUnitController::class, "createMeasurementUnit"]);
    Route::get('get-measurement-unit', [MeasurementUnitController::class, "getMeasurementUnit"]);

    Route::post('create-product', [ProductController::class, "createProduct"]);
    Route::get('get-dashboard-stats', [CommonController::class, "getDashboardStats"]);

    Route::get('get-products', [ProductController::class, "getProducts"]);

    Route::post('create-product-type', [ProductTypeController::class, "createProductTypes"]);
    Route::get('get-product-types', [ProductTypeController::class, "getProductTypes"]);

    Route::post('create-product-gauge', [ProductGaugeController::class, "createGauge"]);
    Route::get('get-product-gauge', [ProductGaugeController::class, "getGauges"]);
    Route::post('create-order', [OrderController::class, "createOrder"]);
    Route::get('get-orders', [OrderController::class, "getOrders"]);
    Route::get('get-orders-details/{id}', [OrderController::class, "getOderDetails"]);
    Route::post('get-chart-accounts', [GlAccountsController::class, "getChartOfAccounts"]);
    Route::post('get-gl-categories', [GlAccountsController::class, "getLedgerCat"]);
    Route::post('get-gl-sub-categories', [GlAccountsController::class, "getLedgerSubCat"]);
    Route::post('get-gl-type', [GlAccountsController::class, "getLedgerType"]);
    Route::get('get-gl-accounts', [GlAccountsController::class, "glAccounts"]);
    Route::post('update-gl-account', [GlAccountsController::class, "updateGlAcct"]);
    Route::post('gl-search', [GlAccountsController::class, "searchGl"]);
    Route::get('gl-cats', [GlAccountsController::class, "getLedgerCategories"]);
    Route::post('debit-credit-acct', [GlAccountsController::class, "debitCreditGl"]);
    Route::post('create-gl-account', [GlAccountsController::class, "createGlAcct"]);

    Route::post('get-product-details', [ProductController::class, "getProductDetails"]);
    Route::get('download-product-report-pdf', [ProductController::class, "downLoadProductPdfReport"]);
    Route::get('download-product-report-csv', [ProductController::class, "downLoadProductCsvReport"]);


    Route::post('get-gl-history', [GlAccountsController::class, "glAcctHistory"]);
    Route::post('get-gl-overview', [GlAccountsController::class, "glAcctOverView"]);
    Route::post('get-gl-balance', [GlAccountsController::class, "getGlBalances"]);
    Route::get("get-control-accts", [GlAccountsController::class,"getCntrlParamGl"]);
    Route::post("get-income-statement", [GlAccountsController::class, "generateIncomeStatement"]);
    Route::post("get-products-report", [ReportController::class, "getInventoryReport"]);
    Route::post("get-sales-report", [ReportController::class,"getSalesReport"]);
    Route::post("get-balance-sheet", [GlAccountsController::class, "getBalanceSheet"]);
    Route::post("get-inventory-product-report", [ReportController::class, "getInventoryHistoryReport"]);
    Route::post("get-sales-product-report", [ReportController::class, "getSalesHistoryReport"]);
    Route::get("get-institution-details", [InstitutionController::class, "getInstitutionDetails"]);

});
