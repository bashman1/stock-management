<?php

namespace App\Http\Controllers;

use App\Models\GlAccounts;
use App\Models\GlCat;
use App\Models\GlSubCat;
use App\Models\GlType;
use App\Models\GlGenerateAccount;
use App\Models\Branch;
use App\Models\GlBalances;
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

        $conditions = array();
        if(isset($request->acct_type)){
            $conditions['acct_type'] =$request->acct_type;
        }
        if(isset($request->gl_no)){
            $conditions['gl_no'] =$request->gl_no;
        }
        if(isset($request->description)){
            $conditions['description'] = ["ilike %'".$request->description."'%"];
        }

        $glCat=DB::table('gl_cats')->select('*')->where($conditions)->get();
        return $this->genericResponse(true, "Ledger category fetched successfully", 200, $glCat);
    }


    public function getLedgerSubCat(Request $request){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();

        $conditions = array();
        if(isset($request->acct_type)){
            $conditions['acct_type'] =$request->acct_type;
        }
        if(isset($request->gl_no)){
            $conditions['gl_no'] =$request->gl_no;
        }
        if(isset( $request->gl_cat_no)){
            $conditions['gl_cat_no'] = $request->gl_cat_no;
        }
        if(isset($request->description)){
            $conditions['description'] = ["ilike %'".$request->description."'%"];
        }
        $glSubCat=DB::table('gl_sub_cats')->select('*')->where($conditions)->get();
        return $this->genericResponse(true, "Ledger sub category fetched successfully", 200, $glSubCat);
    }

    public function getLedgerType(Request $request){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();

        $conditions = array();
        if(isset($request->acct_type)){
            $conditions['acct_type'] =$request->acct_type;
        }
        if(isset($request->gl_no)){
            $conditions['gl_no'] =$request->gl_no;
        }
        if(isset($request->gl_sub_cat_no)){
            $conditions['gl_sub_cat_no'] =$request->gl_sub_cat_no;
        }
        if(isset( $request->gl_cat_no)){
            $conditions['gl_cat_no'] = $request->gl_cat_no;
        }
        if(isset($request->description)){
            $conditions['description'] = ["ilike %'".$request->description."'%"];
        }

        $glType = DB::table('gl_types')->select('*')->where($conditions)->get();
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


    public function searchGl(Request $request){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();

        $conditions = array();
        if(isset($request->acct_type)){
            $conditions['G.acct_type'] = $request->acct_type;
        }
        if(isset($request->gl_no)){
            $conditions['G.gl_no'] = $request->gl_no;
        }
        if(isset($request->description)){
            $conditions['G.description'] = ["ilike %".$request->description."%"];
        }
        if(isset($request->acct_no)){
            $conditions['G.acct_no'] = $request->acct_no;
        }
        if($isNotAdmin){
            $conditions['G.institution_id'] = $userData->institution_id;
        }
        $results = DB::table('gl_accounts as G')
        ->select('G.acct_no', 'G.gl_no', 'G.description', 'G.gl_cat_no', 'G.gl_sub_cat_no', 'G.gl_type_no', 'G.acct_type', 'G.branch_cd', 'G.status',  
            'G.institution_id', 'G.branch_id', 'G.created_by', 'G.updated_by', 'G.created_on', 'G.updated_on', 'G.created_at', 'G.updated_at',
            'T.description AS type_desc', 'S.description AS sub_cat_desc', 'C.description AS cat_desc', 'I.name AS institution_name',
            'B.name AS branch_name', 'K.balance')
        ->join('gl_types as T', 'G.gl_type_no', '=', 'T.gl_no')
        ->join('gl_sub_cats as S', 'S.gl_no', '=', 'G.gl_sub_cat_no')
        ->join('gl_cats as C', 'C.gl_no', '=', 'G.gl_cat_no')
        ->join('institutions as I', 'I.id', '=', 'G.institution_id')
        ->join('branches as B', 'B.id', '=', 'G.branch_id')
        ->join('gl_balances as K', 'K.acct_no', '=', 'G.acct_no')
        ->where($conditions)->get();

        return $this->genericResponse(true, "Chart of accounts fetched successfully", 201, $results);
    }


    public function getLedgerCategories(){
        $cat = GlCat::all();
        return $this->genericResponse(true, "Chart of accounts categories fetched successfully", 200, $cat);
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


    public function debitCreditGl(Request $request){
        $userData = auth()->user();
        DB::beginTransaction();
        $tran = (object)[
            "acct_no"=>$request->drAcctNo,
            "acct_type"=>$request->drAcctType,
            "contra_acct_no"=>$request->crAcctNo,
            "contra_acct_type"=>$request->crAcctType,
            "description"=>$request->description,
            "dr_cr_ind"=>"DR/CR",
            "tran_amount"=>$request->tranAmt,
            "reversal_flag"=>"N",
            "tran_date"=>$request->tranDate,
            "tran_cd"=>"GLI",
            "tran_id"=>$this->generateUuid(),
            "status"=>$request->status,
            "institution_id"=>$userData->institution_id,
            "branch_id"=>$userData->branch_id,
            "created_by"=>$userData->id,
            "created_on"=>now(),
        ];

        $postTran = $this->postTransaction($tran);

        $debitRequest=(object)[
            "acct_no"=>$request->drAcctNo,
            "acct_type"=>$request->drAcctType,
            "tran_amt"=>$request->tranAmt,
            "reversal_flag"=>'N',
            "description"=>$request->description,
            "transaction_date"=>$request->tranDate,
            "contra_acct_no"=>$request->crAcctNo,
            "contra_acct_type"=>$request->crAcctType,
            "tran_type"=>'GL INJECTION',
            "tran_cd"=>'GLI',
            "tran_id"=>$postTran->tran_id,
            "institution_id"=>$userData->institution_id,
            "branch_id"=>$userData->branch_id,
            "created_by"=>$userData->id,
            "status"=>$request->status,
        ];

        $creditRequest=(object)[
            "acct_no"=>$request->crAcctNo,
            "acct_type"=>$request->crAcctType,
            "tran_amt"=>$request->tranAmt,
            "reversal_flag"=>'N',
            "description"=>$request->description,
            "transaction_date"=>$request->tranDate,
            "contra_acct_no"=>$request->drAcctNo,
            "contra_acct_type"=>$request->drAcctType,
            "tran_type"=>'GL INJECTION',
            "tran_cd"=>'GLI',
            "tran_id"=>$postTran->tran_id,
            "institution_id"=>$userData->institution_id,
            "branch_id"=>$userData->branch_id,
            "created_by"=>$userData->id,
            "status"=>$request->status,
        ];
        $debit = $this->postGlDR($debitRequest);
        $credit = $this->postGlCR($creditRequest);
        DB::commit();
        return $this->genericResponse(true, "Ledger account updated successfully", 201,['debit'=>$debitRequest, 'credit'=>$creditRequest]);
    }


    public function createGlAcct(Request $request){
        DB::beginTransaction();
        $userData = auth()->user();
        $genAcct = GlGenerateAccount::where(['gl_cat_no'=>$request->cat, 'gl_sub_cat_no'=>$request->subCat, 'gl_type_no'=>$request->type, 'status'=>$request->status])->first();
        if(!isset($genAcct)){
            return $this->genericResponse(false, "Failed to create a new ledger account", 400, $genAcct);
        }
        $branch = Branch::find($userData->branch_id);

        $glNo = $genAcct->const +$genAcct->current+$genAcct->step;
        $genAcct->current =$genAcct->current+$genAcct->step;
        $genAcct->save();

        $newAcctNo=$this->generateGlAcctNo($userData->institution_id, $branch->code, $glNo);

        $checkExistingGlAcct = GlAccounts::where('acct_no', $newAcctNo)->get();
        // return $checkExistingGlAcct;
        if(!isset($checkExistingGl)  || !empty($checkExistingGl)){
            $glAcct = new GlAccounts();
            $glAcct->gl_no = $glNo;
            $glAcct->acct_no = $newAcctNo;
            $glAcct->description = $request->name;
            $glAcct->gl_cat_no = $request->cat;
            $glAcct->gl_sub_cat_no = $request->subCat;
            $glAcct->gl_type_no = $request->type;
            $glAcct->acct_type = $genAcct->acct_type;
            $glAcct->status = $request->status;
            $glAcct->branch_cd = $branch->code;
            $glAcct->institution_id = $userData->institution_id;
            $glAcct->branch_id = $userData->branch_id;
            $glAcct->created_by = $userData->id;
            $glAcct->created_on = now();
            $glAcct->save();
        }

        $checkExistingGlBal = GlBalances::where('acct_no', $newAcctNo)->get();
        // return $checkExistingGlBal;
        if(!isset($checkExistingGlBal) || !empty($checkExistingGlBal)){
            $glBal = new GlBalances();
            $glBal->acct_no =  $newAcctNo;
            $glBal->acct_type =$genAcct->acct_type   ;
            $glBal->balance = 0  ;
            $glBal->branch_cd = $branch->code;
            $glBal->status = $request->status;
            $glBal->institution_id = $userData->institution_id;
            $glBal->branch_id = $userData->branch_id;
            $glBal->created_by =$userData->id;
            $glBal->created_on = now();
            $glBal->save();
        }
        DB::commit();
        return $this->genericResponse(true, "Ledger account created successfully", 201, $genAcct);

    }

}


