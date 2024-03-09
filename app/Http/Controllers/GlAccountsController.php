<?php

namespace App\Http\Controllers;

use App\Models\GlAccounts;
use App\Models\GlCat;
use App\Models\GlSubCat;
use App\Models\GlType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlAccountsController extends Controller
{
   

    public function getChartOfAccounts(Request $request) {
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();
        $glCats = GlCat::orderBy('gl_no', 'asc')->get();
        $chartOfAccounts = [];
        foreach ($glCats as $glCat) {
            $catData = $glCat->toArray();
            $catData['subCats'] = [];
            $subCats = GlSubCat::where('gl_cat_no', $glCat->gl_no)->orderBy('gl_no', 'asc')->get();
            foreach ($subCats as $subCat) {
                $subCatData = $subCat->toArray();
                $subCatData['types'] = [];
                $glTypes = GlType::where('gl_sub_cat_no', $subCat->gl_no)->orderBy('gl_no', 'asc')->get();
                foreach ($glTypes as $glType) {
                   $typeData = $glType->toArray();
                   $queryString = "SELECT G.acct_no, G.gl_no, G.description, G.gl_cat_no, G.gl_sub_cat_no, G.gl_type_no, G.acct_type, G.branch_cd, G.status,  
                   G.institution_id, G.branch_id, G.created_by, G.updated_by, G.created_on, G.updated_on, G.created_at, G.updated_at,
                   T.description AS type_desc, S.description AS sub_cat_desc, C.description AS cat_desc, I.name AS institution_name,
                   B.name AS branch_name, K.balance
                   FROM gl_accounts G
                   INNER JOIN gl_types T ON G.gl_type_no = T.gl_no
                   INNER JOIN gl_sub_cats S ON S.gl_no = G.gl_sub_cat_no
                   INNER JOIN gl_cats C ON C.gl_no= G.gl_cat_no
                   INNER JOIN institutions I ON I.id = G.institution_id
                   INNER JOIN branches B ON B.id = G.branch_id
                   INNER JOIN gl_balances K ON K.acct_no = G.acct_no ";
                   $queryString.=" WHERE G.gl_type_no=  '".$glType->gl_no."' ";
                   if($isNotAdmin){
                        $queryString.=" AND G.institution_id = $userData->institution_id  ";
                    }
                   $queryString.=" ORDER BY G.gl_no ASC ";
                   $glAccounts= DB::select($queryString);
                   $typeData['accts'] = $glAccounts;
                   $subCatData['types'][] = $typeData;
                }
                $catData['subCats'][] = $subCatData;
            }
            $chartOfAccounts[] = $catData;
        }
        return $this->genericResponse(true, "Chart of accounts fetched successfully", 201, $chartOfAccounts);
    }


    public function getLedgerCat(Request $request) {
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();
        $query = GlCat::query();
        if ($request->filled(['acct_type', 'gl_no', 'description'])) {
            if ($request->filled('acct_type')) {
                $query->where('acct_type', $request->acct_type);
            }
            if ($request->filled('gl_no')) {
                $query->where('gl_no', $request->gl_no);
            }
            if ($request->filled('description')) {
                $query->where('description', 'like', '%' . $request->description . '%');
            }
        }
        $glCat = $query->get();
        return $this->genericResponse(true, "Ledger category fetched successfully", 200, $glCat);
    }


    public function getLedgerSubCat(Request $request){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();

        $query = GlSubCat::query();
        if ($request->filled(['acct_type', 'gl_no', 'description','gl_cat_no'])) {

            if ($request->filled('acct_type')) {
                $query->where('acct_type', $request->acct_type);
            }
            if ($request->filled('gl_no')) {
                $query->where('gl_no', $request->gl_no);
            }
            if ($request->filled('gl_cat_no')) {
                $query->where('gl_cat_no', $request->gl_cat_no);
            }
            if ($request->filled('description')) {
                $query->where('description', 'like', '%' . $request->description . '%');
            }
        }
        $glSubCat = $query->get();
        return $this->genericResponse(true, "Ledger sub category fetched successfully", 200, $glSubCat);
    }

    public function getLedgerType(Request $request){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();
        $query = GlType::query();
        if ($request->filled(['acct_type', 'gl_no', 'description','gl_cat_no, gl_sub_cat_no'])) {
            if ($request->filled('acct_type')) {
                $query->where('acct_type', $request->acct_type);
            }
            if ($request->filled('gl_no')) {
                $query->where('gl_no', $request->gl_no);
            }
            if ($request->filled('gl_sub_cat_no')) {
                $query->where('gl_no', $request->gl_no);
            }
            if ($request->filled('gl_cat_no')) {
                $query->where('gl_cat_no', $request->gl_cat_no);
            }
            if ($request->filled('description')) {
                $query->where('description', 'like', '%' . $request->description . '%');
            }
        }
        $glType = $query->get();
        return $this->genericResponse(true, "Ledger Types fetched successfully", 200, $glType);

    }


    public function glAccounts(){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();

        $queryString = "SELECT G.acct_no, G.gl_no, G.description, G.gl_cat_no, G.gl_sub_cat_no, G.gl_type_no, G.acct_type, G.branch_cd, G.status,  
        G.institution_id, G.branch_id, G.created_by, G.updated_by, G.created_on, G.updated_on, G.created_at, G.updated_at,
        T.description AS type_desc, S.description AS sub_cat_desc, C.description AS cat_desc, I.name AS institution_name,
        B.name AS branch_name, K.balance
        FROM gl_accounts G
        INNER JOIN gl_types T ON G.gl_type_no = T.gl_no
        INNER JOIN gl_sub_cats S ON S.gl_no = G.gl_sub_cat_no
        INNER JOIN gl_cats C ON C.gl_no= G.gl_cat_no
        INNER JOIN institutions I ON I.id = G.institution_id
        INNER JOIN branches B ON B.id = G.branch_id
        INNER JOIN gl_balances K ON K.acct_no = G.acct_no ";
        if($isNotAdmin){
             $queryString.=" WHERE G.institution_id = $userData->institution_id";
         }
        $queryString.=" ORDER BY G.gl_no ASC ";
        $glAccounts= DB::select($queryString);
        return $this->genericResponse(true, "Chart of accounts fetched successfully", 201, $glAccounts);
    }


    public function updateGlAcct(Request $request){
        $userData = auth()->user();
        $request->validate([
            'description'=>'required',
            'acctNo'=>'required'
        ]);
        $glAcct=GlAccounts::where('acct_no', $request->acctNo)->first();
        if(!isset($glAcct)){
            return $this->genericResponse(false, "Ledger account not found", 400, $glAcct);
        }
        $glAcct->description = $request->description;
        $glAcct->updated_by = $userData->id;
        $glAcct->updated_on = now();
        $glAcct->save();

        return $this->genericResponse(true, "Ledger account updated successfully", 201, $glAcct);
    }

}


