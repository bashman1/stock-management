<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use Illuminate\Support\Str;
use App\Models\InstitutionConfig;
use App\Models\GlType;
use App\Models\GlAcctBk;
use App\Models\GlGenerateAccount;
use App\Models\GlAccounts;
use App\Models\GlBalances;
use App\Models\GlCat;
use App\Models\GlSubCat;
use App\Models\CntrlParameter;
use App\Models\GlHistory;
use App\Models\Transaction;

// use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;



        /**
     * For returning responses of the application 25 oct 2022
     *
     * @param [type] $status
     * @param [type] $message
     * @param [type] $data
     * @return void
     * @author Bashir <wamulabash1@gmail.com.com>
     */
    public function genericResponse($status = true, $message, $code = 200, $data = []){
        return response()->json([
            "status"=>$status,
            "code"=>$code,
            "message"=>$message,
            "data"=>$data
        ]);
    }


    /**
     * get current logged in user data  04 Oct 2023
     * @return
     * @author Bashir <wamulabash1@gmail.com>
     */
    public function getUserLogin(){
        return auth()->user();
    }


    /**
     * Generating the member number staring letters
     *
     * @param [type] $input
     * @return void
     * @author Bashir <wamulabash1@gmail.com>
     */
    public function getLetters($input) {
        $words = explode(' ', $input);
        $result = '';
        foreach ($words as $word) {
            $result .= strtoupper(substr($word, 0, min(1, strlen($word))));
        }
        return  substr($result, 0, 2);
    }


    /**
     * check if its admin
     */
    public function isNotAdmin(){
        $userData = auth()->user();
        if ($userData->institution_id != null) {
             return true;
        }
        return false;
    }


    /**
     * Generate branch code
     */
    public function generateBranchCode(){
        $randomCode='';
        do {
            $randomCode = (string) $this->randomThreeDigitCode(3);
            $branchCd = Branch::where("code", $randomCode)->first();
        } while ((isset($branchCd) && !empty($branchCd)) ||  strlen($randomCode)!=3);

        return strtoupper($randomCode);
    }

    /**
     * Generate 3 digit code
     * @return void
     * @author Bashir <wamulabash1@@gmail.com>
     */
    public function randomThreeDigitCode($length){
        $charset = 'abcdefghjklmnpqrstuvwxyz23456789';
        $randomCode = '';
        $charsetLength = strlen($charset);
        for ($i = 0; $i < $length; $i++) {
            $randomCode .= $charset[mt_rand(0, $charsetLength - 1)];
        }
        return  $randomCode;
    }


    /**
     * generate tran ref
     */
    public function generateTranRef(){
        $tranIdRef = InstitutionConfig::where("type", "tran_id_ref")->first();
        $tranId = $tranIdRef->prefix . '' . $tranIdRef->starting . '' . $tranIdRef->current;
        $tranIdRef->current = $tranIdRef->current + $tranIdRef->step;
        $tranIdRef->save();
        return $tranId;
    }

    public function generateUuid(){
        return (string) Str::uuid();
    }


    /**
     * generate Gl accounts
     *
     */
    public function createBranchGlAccounts($instId, $branchCd, $branchId){
        DB::beginTransaction();
        $glTypes=GlAcctBk::all();
        foreach ($glTypes as $value) {
            $genAcctNo= "instId-braCd-000-000-glNo";
            $acctNo = str_replace('instId', $instId, $genAcctNo);
            $acctNo = str_replace('braCd', $branchCd, $acctNo);
            $acctNo = str_replace('glNo', $value['gl_no'], $acctNo);

            $checkExistingGlAcct = GlAccounts::where('acct_no', $acctNo)->get();
            if(!isset($checkExistingGl)  || !empty($checkExistingGl)){
                $glAcct = new GlAccounts();
                $glAcct->gl_no = $value['gl_no'];
                $glAcct->acct_no = $acctNo;
                $glAcct->description = $value['description'];
                $glAcct->gl_cat_no = $value['gl_cat_no'];
                $glAcct->gl_sub_cat_no = $value['gl_sub_cat_no'];
                $glAcct->gl_type_no = $value['gl_type_no'];
                $glAcct->acct_type = $value['acct_type'];
                $glAcct->status = $value['status'];
                $glAcct->branch_cd = $branchCd;
                $glAcct->institution_id = $instId;
                $glAcct->branch_id = $branchId;
                $glAcct->created_on = now();
                $glAcct->save();
            }
            $checkExistingGlBal = GlBalances::where('acct_no', $acctNo)->get();
            if(!isset($checkExistingGlBal) || !empty($checkExistingGlBal)){
                $glBal = new GlBalances();
                $glBal->acct_no =  $acctNo;
                $glBal->acct_type =$value['acct_type']   ;
                $glBal->balance = 0  ;
                $glBal->branch_cd = $branchCd;
                $glBal->status = $value['status'];
                $glBal->institution_id = $instId;
                $glBal->branch_id = $branchId;
                $glBal->created_on = now();
                $glBal->save();
            }
        }
        DB::commit();
        return "success";
    }


    public function setControlParam($instId){
        DB::beginTransaction();
        $cl = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'ASSET')
            ->where('gl_no', '1301001')
            ->first();

        if(!isset($cl)){return false;}
        $isClExisting =  CntrlParameter::where(["institution_id"=>$instId, "param_value" => strrev($cl->replaced_acct_no)])->exists();
        if(!$isClExisting){
            $newCl= new CntrlParameter();
            $newCl->param_name = "Cash Ledger";
            $newCl->param_cd="CL";
            $newCl->param_value=strrev($cl->replaced_acct_no);
            $newCl->institution_id=$instId;
            $newCl->created_on=now();
            $newCl->save();
        }

        $cgl =DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'ASSET')
            ->where('gl_no', '1302001')
            ->first();

        if(!isset($cgl)){return false;}
        $isCglExisting =  CntrlParameter::where(["institution_id"=>$instId, "param_value" => strrev($cgl->replaced_acct_no)])->exists();
        if(!$isCglExisting){
            $newCgl= new CntrlParameter();
            $newCgl->param_name = "General Cash Ledger";
            $newCgl->param_cd="CGL";
            $newCgl->param_value=strrev($cgl->replaced_acct_no);
            $newCgl->institution_id=$instId;
            $newCgl->created_on=now();
            $newCgl->save();
        }

        $sti =DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'ASSET')
            ->where('gl_no', '1304001')
            ->first();

        if(!isset($sti)){return false;}
        $isStiExisting =  CntrlParameter::where(["institution_id"=>$instId, "param_value" => strrev($sti->replaced_acct_no)])->exists();
        if(!$isStiExisting){
            $newSti= new CntrlParameter();
            $newSti->param_name = "Inventory and Stock";
            $newSti->param_cd="STI";
            $newSti->param_value=strrev($sti->replaced_acct_no);
            $newSti->institution_id=$instId;
            $newSti->created_on=now();
            $newSti->save();
        }

        $sl=DB::table('gl_accounts')
        ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
        ->where('institution_id', $instId)
        ->where('acct_type', 'INCOME')
        ->where('gl_no', '4040001')
        ->first();

        $isSlExisting =  CntrlParameter::where(["institution_id"=>$instId, "param_value" => strrev($sl->replaced_acct_no)])->exists();
        if(!$isSlExisting){
            $newSti= new CntrlParameter();
            $newSti->param_name = "Sales";
            $newSti->param_cd="SL";
            $newSti->param_value=strrev($sl->replaced_acct_no);
            $newSti->institution_id=$instId;
            $newSti->created_on=now();
            $newSti->save();
        }
        // set purchases assets and purchases liability  Purchases in assets PIA
        // asset 1305001  liability 2205001
        $pia=DB::table('gl_accounts')
        ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
        ->where('institution_id', $instId)
        ->where('acct_type', 'ASSET')
        ->where('gl_no', '1305001')
        ->first();

        $isPiaExisting =  CntrlParameter::where(["institution_id"=>$instId, "param_value" => strrev($pia->replaced_acct_no)])->exists();
        if(!$isPiaExisting){
            $newSti= new CntrlParameter();
            $newSti->param_name = "Purchases in assets PIA";
            $newSti->param_cd="PIA";
            $newSti->param_value=strrev($pia->replaced_acct_no);
            $newSti->institution_id=$instId;
            $newSti->created_on=now();
            $newSti->save();
        }

        $pil=DB::table('gl_accounts')
        ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
        ->where('institution_id', $instId)
        ->where('acct_type', 'LIABILITY')
        ->where('gl_no', '2205001')
        ->first();

        $isPilExisting =  CntrlParameter::where(["institution_id"=>$instId, "param_value" => strrev($pil->replaced_acct_no)])->exists();
        if(!$isPilExisting){
            $newSti= new CntrlParameter();
            $newSti->param_name = "Purchases in liability PIL";
            $newSti->param_cd="PIL";
            $newSti->param_value=strrev($pil->replaced_acct_no);
            $newSti->institution_id=$instId;
            $newSti->created_on=now();
            $newSti->save();
        }

        // set passage expenses   5020001
        $pp=DB::table('gl_accounts')
        ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
        ->where('institution_id', $instId)
        ->where('acct_type', 'EXPENSE')
        ->where('gl_no', '5020001')
        ->first();

        $isPpExisting =  CntrlParameter::where(["institution_id"=>$instId, "param_value" => strrev($pp->replaced_acct_no)])->exists();
        if(!$isPpExisting){
            $newSti= new CntrlParameter();
            $newSti->param_name = "Purchases passage PP";
            $newSti->param_cd="PP";
            $newSti->param_value=strrev($pp->replaced_acct_no);
            $newSti->institution_id=$instId;
            $newSti->created_on=now();
            $newSti->save();
        }

        // set purchase discount income   4050001
        $pd=DB::table('gl_accounts')
        ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
        ->where('institution_id', $instId)
        ->where('acct_type', 'INCOME')
        ->where('gl_no', '4050001')
        ->first();

        $isPdExisting =  CntrlParameter::where(["institution_id"=>$instId, "param_value" => strrev($pd->replaced_acct_no)])->exists();
        if(!$isPdExisting){
            $newSti= new CntrlParameter();
            $newSti->param_name = "purchase discount PD";
            $newSti->param_cd="PD";
            $newSti->param_value=strrev($pd->replaced_acct_no);
            $newSti->institution_id=$instId;
            $newSti->created_on=now();
            $newSti->save();
        }
        DB::commit();
        return true;
    }


    /**
     * Debit the gl account
     */
    public function postGlDR($data){
        DB::beginTransaction();
        $glBalances = GlBalances::where('acct_no', $data->acct_no)->first();

        if($data->acct_type=='ASSET' || $data->acct_type=='EXPENSE'){
            $glBalances->balance=$glBalances->balance+$data->tran_amt;
        }else{
            $glBalances->balance=$glBalances->balance-$data->tran_amt;
        }
        $glBalances->save();

        $glHistory = new GlHistory();
        $glHistory->acct_no =$data->acct_no ;
        $glHistory->dr_cr_ind = 'Dr';
        $glHistory->tran_amount=$data->tran_amt;
        $glHistory->reversal_flag = $data->reversal_flag;
        $glHistory->description = $data->description;
        $glHistory->transaction_date = $data->transaction_date;
        $glHistory->contra_acct_no = $data->contra_acct_no;
        $glHistory->contra_acct_type = $data->contra_acct_type;
        $glHistory->tran_type = $data->tran_type;
        $glHistory->tran_id = $data->tran_id;
        $glHistory->tran_cd = $data->tran_cd;
        $glHistory->status = $data->status;
        $glHistory->institution_id = $data->institution_id;
        $glHistory->branch_id = $data->branch_id;
        $glHistory->created_by = $data->created_by;
        $glHistory->created_on =now() ;
        $glHistory->save() ;
        DB::commit();

        return $glHistory;
    }


    /**
     * credit the gl account
     */
    public function postGlCR($data){
        DB::beginTransaction();
        $glBalances = GlBalances::where('acct_no', $data->acct_no)->first();

        if($data->acct_type=='ASSET' || $data->acct_type=='EXPENSE'){
            $glBalances->balance=$glBalances->balance-$data->tran_amt;
        }else{
            $glBalances->balance=$glBalances->balance+$data->tran_amt;
        }
        $glBalances->save();

        $glHistory = new GlHistory();
        $glHistory->acct_no =$data->acct_no ;
        $glHistory->dr_cr_ind = 'Cr';
        $glHistory->tran_amount=$data->tran_amt;
        $glHistory->reversal_flag = $data->reversal_flag;
        $glHistory->description = $data->description;
        $glHistory->transaction_date = $data->transaction_date;
        $glHistory->contra_acct_no = $data->contra_acct_no;
        $glHistory->contra_acct_type = $data->contra_acct_type;
        $glHistory->tran_type = $data->tran_type;
        $glHistory->tran_id = $data->tran_id;
        $glHistory->tran_cd = $data->tran_cd;
        $glHistory->status = $data->status;
        $glHistory->institution_id = $data->institution_id;
        $glHistory->branch_id = $data->branch_id;
        $glHistory->created_by = $data->created_by;
        $glHistory->created_on =now() ;
        $glHistory->save() ;
        DB::commit();
        return $glHistory;
    }


    /**
     * Generate GL number for a branch
     */
    public function generateGlAcctNo($instId, $branchCd, $gl_no){
        $genAcctNo= "instId-braCd-000-000-glNo";
        $acctNo = str_replace('instId', $instId, $genAcctNo);
        $acctNo = str_replace('braCd', $branchCd, $acctNo);
        $acctNo = str_replace('glNo', $gl_no, $acctNo);
        return $acctNo;
    }


    /**
     * post transaction
     *
     */
    public function postTransaction($transactions){
        $transaction = new Transaction();
        DB::beginTransaction();
        $transaction->acct_no = $transactions->acct_no  ;
        $transaction->acct_type = $transactions->acct_type  ;
        $transaction->contra_acct_no = $transactions->contra_acct_no  ;
        $transaction->contra_acct_type = $transactions->contra_acct_type  ;
        $transaction->description = $transactions->description  ;
        $transaction->dr_cr_ind = $transactions->dr_cr_ind  ;
        $transaction->tran_amount = $transactions->tran_amount  ;
        $transaction->reversal_flag = $transactions->reversal_flag  ;
        $transaction->tran_date = $transactions->tran_date  ;
        $transaction->tran_cd = $transactions->tran_cd  ;
        $transaction->tran_id = $transactions->tran_id  ;
        $transaction->status = $transactions->status  ;
        $transaction->institution_id = $transactions->institution_id ;
        $transaction->branch_id = $transactions->branch_id  ;
        $transaction->created_by = $transactions->created_by  ;
        $transaction->created_on = $transactions->created_on  ;
        $transaction->save();
        DB::commit();
        return $transaction;
    }
}
