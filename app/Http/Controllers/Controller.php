<?php

namespace App\Http\Controllers;

use App\Models\OutGoingMail;
use App\Models\Role;
use App\Models\SystemLog;
use App\Services\MailSenderService;
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
use App\Models\CustomerReceivable;
use App\Models\DefaultRole;
use App\Models\DefaultRolePermission;
use App\Models\GlHistory;
use App\Models\Payable;
use App\Models\RolePermission;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailSender;
use Illuminate\Support\Facades\Request as IpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

// use

// use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $mailSenderService;

    public function __construct(MailSenderService $mailSenderService)
    {
        $this->mailSenderService = $mailSenderService; // Correctly assigning the parameter to the property
    }


    /**
     * For returning responses of the application 25 oct 2022
     *
     * @param [type] $status
     * @param [type] $message
     * @param [type] $data
     * @return void
     * @author Bashir <wamulabash1@gmail.com.com>
     */
    public function genericResponse($status, $message, $code, $data, $action = null, $request = null)
    {

        $log = [
            "action" => $action,
            "ip" => IpRequest::ip(),
            "http_code" => $code,
            "request" => $request instanceof Request ? $request->all() : $request,
            "response" => $data,
            "return_status" => $status,
            "return_message" => $message,
            "user_id" => auth()->user() ? auth()->user()->id : null,
            "institution_id" => auth()->user() ? auth()->user()->institution_id : null,
            "branch_id" => auth()->user() ? auth()->user()->branch_id : null,
            "created_on" => now()
        ];
        $this->setLogs($log);


        return response()->json([
            "status" => $status,
            "code" => $code,
            "message" => $message,
            "data" => $data
        ], $code);
    }


    /**
     * get current logged in user data  04 Oct 2023
     * @return
     * @author Bashir <wamulabash1@gmail.com>
     */
    public function getUserLogin()
    {
        return auth()->user();
    }


    /**
     * Generating the member number staring letters
     *
     * @param [type] $input
     * @return void
     * @author Bashir <wamulabash1@gmail.com>
     */

    public function getLetters(string $input): string
    {
        return substr(implode('', array_map(fn($word) => strtoupper($word[0] ?? ''), explode(' ', $input))), 0, 2);
    }



    /**
     * check if its admin
     *
     * @return boolean
     */

    public function isNotAdmin(): bool
    {
        return auth()->user()->institution_id !== null;
    }


    /**
     * Generate branch code
     *
     * @return void
    //  */
    public function generateBranchCode(): string
    {
        do {
            $randomCode = str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT); // Generate a 3-digit code
            $branchExists = Branch::where('code', $randomCode)->exists();    // Use `exists()` for efficient DB check
        } while ($branchExists);

        return strtoupper($randomCode);
    }


    /**
     * Generate 3 digit code
     * @return void
     * @author Bashir <wamulabash1@@gmail.com>
     */
    // public function randomThreeDigitCode($length): string
    // {
    //     $charset = 'abcdefghjklmnpqrstuvwxyz23456789';
    //     $randomCode = '';
    //     $charsetLength = strlen($charset);
    //     for ($i = 0; $i < $length; $i++) {
    //         $randomCode .= $charset[mt_rand(0, $charsetLength - 1)];
    //     }
    //     return  $randomCode;
    // }
    public function randomThreeDigitCode(int $length): string
    {
        $charset = 'abcdefghjklmnpqrstuvwxyz23456789';
        $charsetLength = strlen($charset);

        return implode('', array_map(fn() => $charset[random_int(0, $charsetLength - 1)], range(1, $length)));
    }



    /**
     * generate tran ref
     *
     * @return void
     */

    public function generateTranRef(): string
    {
        $tranIdRef = InstitutionConfig::where('type', 'tran_id_ref')->firstOrFail();

        $tranId = sprintf('%s%s%s', $tranIdRef->prefix, $tranIdRef->starting, $tranIdRef->current);

        $tranIdRef->increment('current', $tranIdRef->step);

        return $tranId;
    }


    /**
     * generate the ref number
     *
     * @param [type] $type
     * @return void
     */
    public function generateRefNumber(string $type): string
    {
        $refNo = InstitutionConfig::where('type', $type)->firstOrFail();

        $generatedCode = sprintf('%s%d', $refNo->prefix, $refNo->starting + $refNo->current);

        $refNo->increment('current', $refNo->step);

        return $generatedCode;
    }



    /**
     * generate UUID
     *
     * @return void
     */
    public function generateUuid(): string
    {
        return (string) Str::uuid();
    }


    /**
     * generate Gl accounts
     *
     * @param [type] $instId
     * @param [type] $branchCd
     * @param [type] $branchId
     * @return void
     * @author bsh <email>
     */
    public function createBranchGlAccounts($instId, $branchCd, $branchId)
    {
        DB::beginTransaction();
        $glTypes = GlAcctBk::all();
        foreach ($glTypes as $value) {
            $genAcctNo = "instId-braCd-000-000-glNo";
            $acctNo = str_replace('instId', $instId, $genAcctNo);
            $acctNo = str_replace('braCd', $branchCd, $acctNo);
            $acctNo = str_replace('glNo', $value['gl_no'], $acctNo);

            $checkExistingGlAcct = GlAccounts::where('acct_no', $acctNo)->get();
            if (!isset($checkExistingGlAcct)  || !empty($checkExistingGlAcct)) {
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
            if (!isset($checkExistingGlBal) || !empty($checkExistingGlBal)) {
                $glBal = new GlBalances();
                $glBal->acct_no =  $acctNo;
                $glBal->acct_type = $value['acct_type'];
                $glBal->balance = 0;
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

    /**
     * set control gls
     *
     * @param [type] $instId
     * @return void
     * @author bsh <email>
     */
    public function setControlParam($instId): bool
    {
        DB::beginTransaction();
        $cl = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'ASSET')
            ->where('gl_no', '1301001')
            ->first();

        if (!isset($cl)) {
            return false;
        }
        $isClExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($cl->replaced_acct_no)])->exists();
        if (!$isClExisting) {
            $newCl = new CntrlParameter();
            $newCl->param_name = "Cash Ledger";
            $newCl->param_cd = "CL";
            $newCl->param_value = strrev($cl->replaced_acct_no);
            $newCl->institution_id = $instId;
            $newCl->created_on = now();
            $newCl->save();
        }

        $cgl = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'ASSET')
            ->where('gl_no', '1302001')
            ->first();

        if (!isset($cgl)) {
            return false;
        }
        $isCglExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($cgl->replaced_acct_no)])->exists();
        if (!$isCglExisting) {
            $newCgl = new CntrlParameter();
            $newCgl->param_name = "General Cash Ledger";
            $newCgl->param_cd = "CGL";
            $newCgl->param_value = strrev($cgl->replaced_acct_no);
            $newCgl->institution_id = $instId;
            $newCgl->created_on = now();
            $newCgl->save();
        }

        $sti = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'ASSET')
            ->where('gl_no', '1304001')
            ->first();

        if (!isset($sti)) {
            return false;
        }
        $isStiExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($sti->replaced_acct_no)])->exists();
        if (!$isStiExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Inventory and Stock";
            $newSti->param_cd = "STI";
            $newSti->param_value = strrev($sti->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }

        $sl = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'INCOME')
            ->where('gl_no', '4040001')
            ->first();

        $isSlExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($sl->replaced_acct_no)])->exists();
        if (!$isSlExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Sales";
            $newSti->param_cd = "SL";
            $newSti->param_value = strrev($sl->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }
        // set purchases assets and purchases liability  Purchases in assets PIA
        // asset 1305001  liability 2205001
        $pia = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'ASSET')
            ->where('gl_no', '1305001')
            ->first();

        $isPiaExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($pia->replaced_acct_no)])->exists();
        if (!$isPiaExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Purchases in assets PIA";
            $newSti->param_cd = "PIA";
            $newSti->param_value = strrev($pia->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }

        $pil = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'LIABILITY')
            ->where('gl_no', '2205001')
            ->first();

        $isPilExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($pil->replaced_acct_no)])->exists();
        if (!$isPilExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Purchases in liability PIL";
            $newSti->param_cd = "PIL";
            $newSti->param_value = strrev($pil->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }

        // set passage expenses   5020001
        $pp = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'EXPENSE')
            ->where('gl_no', '5020001')
            ->first();

        $isPpExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($pp->replaced_acct_no)])->exists();
        if (!$isPpExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Purchases passage PP";
            $newSti->param_cd = "PP";
            $newSti->param_value = strrev($pp->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }

        // set purchase discount income   4050001
        $pd = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'INCOME')
            ->where('gl_no', '4050001')
            ->first();

        $isPdExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($pd->replaced_acct_no)])->exists();
        if (!$isPdExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "purchase discount PD";
            $newSti->param_cd = "PD";
            $newSti->param_value = strrev($pd->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }

        // set Return inwards Expense
        $rin = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'EXPENSE')
            ->where('gl_no', '5010001')
            ->first();

        $isRinExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($rin->replaced_acct_no)])->exists();
        if (!$isRinExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Return inwards(sales returns) RIN";
            $newSti->param_cd = "RIN";
            $newSti->param_value = strrev($rin->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }

        // set Return outwards Expense
        $rot = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'EXPENSE')
            ->where('gl_no', '5030001')
            ->first();

        $isRotExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($rot->replaced_acct_no)])->exists();
        if (!$isRotExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Return outwards(purchases returns) ROT";
            $newSti->param_cd = "ROT";
            $newSti->param_value = strrev($rot->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }

        // set operating expenses Expense
        $ope = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'EXPENSE')
            ->where('gl_no', '5040001')
            ->first();

        $isOpeExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($ope->replaced_acct_no)])->exists();
        if (!$isOpeExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Operating expenses OPE";
            $newSti->param_cd = "OPE";
            $newSti->param_value = strrev($ope->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }

        $gfs = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'ASSET')
            ->where('gl_no', '1307001')
            ->first();

        $isGfsExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($gfs->replaced_acct_no)])->exists();
        if (!$isGfsExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Goods for sale GFS";
            $newSti->param_cd = "GFS";
            $newSti->param_value = strrev($gfs->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }

        $tax = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'LIABILITY')
            ->where('gl_no', '2203001')
            ->first();

        $isTaxExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($tax->replaced_acct_no)])->exists();
        if (!$isTaxExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Income taxes payable TAX";
            $newSti->param_cd = "TAX";
            $newSti->param_value = strrev($tax->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }


        //        receivalbe
        $rcbl = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'ASSET')
            ->where('gl_no', '1303001')
            ->first();

        $isRcblExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($rcbl->replaced_acct_no)])->exists();
        if (!$isRcblExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Account Receivable AR";
            $newSti->param_cd = "AR";
            $newSti->param_value = strrev($rcbl->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }

        //        payable
        $pybl = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'LIABILITY')
            ->where('gl_no', '2202001')
            ->first();

        $isPyblExisting =  CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($pybl->replaced_acct_no)])->exists();
        if (!$isPyblExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Account Payable AP";
            $newSti->param_cd = "AP";
            $newSti->param_value = strrev($pybl->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }



        DB::commit();
        return true;
    }


    /**
     * Debit the gl account
     *
     * @param [type] $data
     * @return void
     * @author bsh <email>
     */
    public function postGlDR($data): GlHistory
    {
        DB::beginTransaction();
        $glBalances = GlBalances::where('acct_no', $data->acct_no)->first();

        if ($data->acct_type == 'ASSET' || $data->acct_type == 'EXPENSE') {
            $glBalances->balance = $glBalances->balance + $data->tran_amt;
        } else {
            $glBalances->balance = $glBalances->balance - $data->tran_amt;
        }
        $glBalances->save();

        $glHistory = new GlHistory();
        $glHistory->acct_no = $data->acct_no;
        $glHistory->dr_cr_ind = 'Dr';
        $glHistory->tran_amount = (float) $data->tran_amt;
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
        $glHistory->created_on = now();
        $glHistory->save();
        DB::commit();

        return $glHistory;
    }


    /**
     * credit the gl account
     *
     * @param [type] $data
     * @return void
     * @author bsh <email>
     */
    public function postGlCR($data): GlHistory
    {
        DB::beginTransaction();
        $glBalances = GlBalances::where('acct_no', $data->acct_no)->first();

        if ($data->acct_type == 'ASSET' || $data->acct_type == 'EXPENSE') {
            $glBalances->balance = $glBalances->balance - $data->tran_amt;
        } else {
            $glBalances->balance = $glBalances->balance + $data->tran_amt;
        }
        $glBalances->save();

        $glHistory = new GlHistory();
        $glHistory->acct_no = $data->acct_no;
        $glHistory->dr_cr_ind = 'Cr';
        $glHistory->tran_amount = (float) $data->tran_amt;
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
        $glHistory->created_on = now();
        $glHistory->save();
        DB::commit();
        return $glHistory;
    }


    /**
     * Generate GL number for a branch
     *
     * @param [type] $instId
     * @param [type] $branchCd
     * @param [type] $gl_no
     * @return void
     * @author bsh <email>
     */
    // public function generateGlAcctNo($instId, $branchCd, $gl_no): string
    // {
    //     $genAcctNo = "instId-braCd-000-000-glNo";
    //     $acctNo = str_replace('instId', $instId, $genAcctNo);
    //     $acctNo = str_replace('braCd', $branchCd, $acctNo);
    //     $acctNo = str_replace('glNo', $gl_no, $acctNo);
    //     return $acctNo;
    // }
    public function generateGlAcctNo($instId, $branchCd, $gl_no): string
    {
        return sprintf('%s-%s-000-000-%s', $instId, $branchCd, $gl_no);
    }



    /**
     * post transaction
     *
     * @param [type] $transactions
     * @return void
     * @author bsh <email>
     */
    public function postTransaction($transactions): Transaction
    {
        $transaction = new Transaction();
        DB::beginTransaction();
        $transaction = Transaction::create($transactions);
        // $transaction->acct_no = $transactions->acct_no;
        // $transaction->acct_type = $transactions->acct_type;
        // $transaction->contra_acct_no = $transactions->contra_acct_no;
        // $transaction->contra_acct_type = $transactions->contra_acct_type;
        // $transaction->description = $transactions->description;
        // $transaction->dr_cr_ind = $transactions->dr_cr_ind;
        // $transaction->tran_amount = (float) $transactions->tran_amount;
        // $transaction->reversal_flag = $transactions->reversal_flag;
        // $transaction->tran_date = $transactions->tran_date;
        // $transaction->tran_cd = $transactions->tran_cd;
        // $transaction->tran_id = $transactions->tran_id;
        // $transaction->status = $transactions->status;
        // $transaction->institution_id = $transactions->institution_id;
        // $transaction->branch_id = $transactions->branch_id;
        // $transaction->created_by = $transactions->created_by;
        // $transaction->created_on = $transactions->created_on;
        // $transaction->save();
        DB::commit();
        return $transaction;
    }


    /**
     * get control number
     *
     * @param [type] $code
     * @return void
     * @author bsh <email>
     */
    public function getControlAcctByCode($code): array|string
    {
        $cntrl = CntrlParameter::where(["param_cd" => $code, "institution_id" => auth()->user()->institution_id])->first();
        $branch = Branch::find(auth()->user()->branch_id);
        return str_replace("***", $branch->code, $cntrl->param_value);
    }


    public function createReceivable($receivable)
    {
        $response = CustomerReceivable::create($receivable);
        return $response;
    }

    public function createPayable($payable): Payable
    {
        $response = Payable::create($payable);
        return $response;
    }


    public function getUsersByInstitution($institutionId): array
    {
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();

        $queryString = "SELECT U.id,U.first_name, U.last_name,U.other_name, U.phone_number, U.gender, U.date_of_birth, U.address, U.city_id, U.email,
        U.status, U.user_type, U.user_category, U.street, U.p_o_box, U.description, U.role_id, U.institution_id, U.branch_id, U.created_at,
        U.created_on,U.updated_at, C.name AS city, R.name AS role, I.name AS institution, B.name AS branch,
        CONCAT(V.first_name,' ', V.last_name,' ', V.other_name) as created_by
        FROM users U LEFT JOIN city_refs C ON C.id =U.city_id
        INNER JOIN roles R ON R.id = U.role_id
        INNER JOIN institutions I ON I.id = U.institution_id
        INNER JOIN branches B ON B.id = U.branch_id
        LEFT JOIN users V ON V.id = U.created_by";
        if ($isNotAdmin) {
            $queryString .= " WHERE  U.institution_id =$institutionId  AND U.institution_id = $userData->institution_id ";
        }
        $queryString .= " ORDER BY U.id DESC ";
        return DB::select($queryString);
    }


    public function getGlBalancesAsOf($startDate, $endDate, $acctNo): float|int
    {
        try {
            // Use parameterized query to prevent SQL injection
            $queryString = "SELECT H.*, B.acct_type
                        FROM gl_histories H
                        INNER JOIN gl_balances B ON H.acct_no = B.acct_no
                        WHERE H.acct_no = :acctNo";

            // Add date filtering if both start and end dates are provided
            if ($startDate && $endDate) {
                $queryString .= " AND H.transaction_date::date BETWEEN :startDate AND :endDate";
            }

            $queryString .= " ORDER BY H.id ASC";

            // Bind the parameters
            $bindings = [
                'acctNo' => $acctNo,
                'startDate' => $startDate,
                'endDate' => $endDate
            ];

            // Execute the query and fetch results
            $glHistory = DB::select($queryString, array_filter($bindings));

            // Initialize the sum
            $sum = 0;

            // Loop through the history and calculate the balance
            foreach ($glHistory as $value) {
                $tranAmount = (float) $value->tran_amount;
                if (($value->acct_type == 'ASSET' || $value->acct_type == 'EXPENSE') && $value->dr_cr_ind == 'Dr') {
                    $sum += $tranAmount;
                } elseif (($value->acct_type == 'ASSET' || $value->acct_type == 'EXPENSE') && $value->dr_cr_ind == 'Cr') {
                    $sum -= $tranAmount;
                } elseif (($value->acct_type == 'LIABILITY' || $value->acct_type == 'INCOME') && $value->dr_cr_ind == 'Cr') {
                    $sum += $tranAmount;
                } elseif (($value->acct_type == 'LIABILITY' || $value->acct_type == 'INCOME') && $value->dr_cr_ind == 'Dr') {
                    $sum -= $tranAmount;
                }
            }

            return $sum;
        } catch (\Throwable $th) {
            throw $th; // Rethrow the exception after logging it if needed
        }
    }


    public function createInstitutionDefaultRole($institutionId)
    {
        try {
            DB::beginTransaction();
            $index = 0;
            $userRole = null;
            $defaultRole = DefaultRole::where('status', "Active")->get();
            foreach ($defaultRole as $value) {
                $role = Role::create([
                    "name" => $value["name"],
                    "type" => $value["type"],
                    "institution_id" => $institutionId,
                    "status" => "Active",
                    "role_type" => $value["role_type"],
                    "description" => $value["description"],
                    // "created_by"=> auth()->user()->id,
                    "created_on" => Carbon::now(),
                ]);

                if ($index == 0) {
                    $userRole = $role;
                }

                $defaultRolePermissions = DefaultRolePermission::where("role_id", $value["id"])->get();
                foreach ($defaultRolePermissions as $defaultRolePermission) {
                    $rolePermission = RolePermission::create([
                        "role_id" => $defaultRolePermission["role_id"],
                        "permission_id" => $defaultRolePermission["permission_id"],
                        // "created_by"=>auth()->user()->id,
                        "created_on" => Carbon::now(),
                    ]);
                }
            }
            DB::commit();
            return $userRole;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * Summary of sendMail
     * @param mixed $postData
     * @return void
     */
    public function sendMail($postData)
    {
        // $body = "<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. <br/>
        // Possimus, a aspernatur impedit quos recusandae incidunt inventore,
        // aperiam quis consequatur, doloribus repellat asperiores ratione distinctio iste vero ullam! Dolorum, eum ut! </p>";
        // $postData = ["subject" => "Testing Mail", "body" => $body, "has_attachment" => true,
        // "to"=>["wamulabash1@gmail.com"], "cc"=>[], "bcc"=>[],
        // "attachment"=>$body, "created_on"=>Carbon::now(), "attachment_name"=>"Testing Mail Attachment"];

        // $this->mailSenderService->setOutGoingMails($postData);
        DB::beginTransaction();
        $outGoingMails = OutGoingMail::create($postData);
        DB::commit();
    }

    /**
     * Summary of setLogs
     * @param mixed $log
     * @return TModel
     */
    public function setLogs($log)
    {
        return SystemLog::create($log);
    }


    public function newUser($request)
    {
        $user = null;
        if ($request && isset($request->id) && !empty($request->id)) {
            $user = User::find($request->id);
            $user->updated_at = Carbon::now();
        } else {
            $user = new User();
            $user->created_by = 1;
            $user->created_on = now();
            $user->password = bcrypt($request->password);
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->other_name = $request->other_name;
        $user->email = $request->email;
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
        $user->reset_required = true;
        $user->user_category =  $request->user_category;
        $user->institution_id = $request->institution_id;
        $user->branch_id =  $request->branch_id;
        $user->original_branch_id = $request->branch_id;

        $user->save();
        return $user;
    }


    /**
     * Summary of sendAdminsNotification
     * @param mixed $mailAndSubject
     * @return void
     */
    public function sendAdminsNotification(object $mailAndSubject): void
    {
        try {
            // Fetch active admin emails
            $receivers = User::where('user_type', 'Admin')
                ->where('status', 'Active')
                ->pluck('email')
                ->toArray();

            // Prepare the email payload
            $sendMail = [
                "subject" => $mailAndSubject->subject ?? 'No Subject',
                "body" => $mailAndSubject->body ?? 'No Body',
                "has_attachment" => false,
                "to" => $receivers,
                "cc" => [],
                "bcc" => [],
                "attachment" => "",
                "created_on" => Carbon::now(),
                "attachment_name" => "",
            ];
            // Send the email
            $this->sendMail($sendMail);
        } catch (\Throwable $th) {
            // Log the exception for debugging
            Log::error('Failed to send admin notifications', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
            ]);
            // Optionally re-throw the exception
            throw $th;
        }
    }


    // public function updateTransaction($description, $institutionId, $branchId){
    //     $transaction = Transaction::where(['description'=> $description, 'institution_id'=>$institutionId, 'branch_id'=>$branchId])->first();
    //     $transaction->amount=100;
    //     $transaction->updated_on=Carbon::now();
    //     $transaction->save();
    // }

    public function updateTransaction(int $productId, int $institutionId, int $branchId, float $amount)
    {
        try {
            $transaction = Transaction::where('product_id', $productId)
                ->where('institution_id', $institutionId)
                ->where('branch_id', $branchId)
                ->first();

            if (!$transaction) {
                Log::error("Transaction not found for description: {$productId}, institutionId: {$institutionId}, branchId: {$branchId}");
                throw new \Exception("Transaction not found for description: {$productId}, institutionId: {$institutionId}, branchId: {$branchId}");
            }

            $oldAmount = $transaction->tran_amount;
            $transaction->update([
                'tran_amount' => $amount,
                'updated_on' => Carbon::now(),
            ]);

            $transaction['old_amount']= $oldAmount;
            return $transaction;

        } catch (\Exception $e) {
            Log::error("Failed to update transaction: " . $e->getMessage());
            throw new \Exception("An error occurred while updating the transaction.", 500, $e);
        }
    }


    public function updatePayable(string $tranId, float $amount)
    {
        $payable = Payable::where('tran_id', $tranId)->first();
        if ($payable) {
            $payable->update([
                'amount' => $amount,
                'updated_on' => Carbon::now(),
            ]);
        }
        return $payable;
    }


    public function updateGlHistory(string $tranId, float $amount, float $old_amount)
    {
        $glHistory = GlHistory::where(['tran_id'=> $tranId, 'tran_amount'=> $old_amount])->get();

        Log::info("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~");
        Log::info($glHistory);
        Log::info($tranId);
        Log::info($amount);


        foreach ($glHistory as $value) {
            $glBalance = GlBalances::where('acct_no', $value['acct_no'])->first();


            if ($value['dr_cr_ind'] == 'Dr') {
                if ($glBalance->acct_type == 'ASSET' || $glBalance->acct_type == 'EXPENSE') {
                    $glBalance->update([
                        "balance" => $glBalance->balance - $value['tran_amount'],
                        'updated_on' => Carbon::now(),
                    ]);

                    $glBalance->update([
                        "balance" => $glBalance->balance + $amount,
                        'updated_on' => Carbon::now(),
                    ]);
                } else {
                    $glBalance->update([
                        "balance" => $glBalance->balance + $value['tran_amount'],
                        'updated_on' => Carbon::now(),
                    ]);

                    $glBalance->update([
                        "balance" => $glBalance->balance - $amount,
                        'updated_on' => Carbon::now(),
                    ]);
                }
            } else {
                if ($glBalance->acct_type == 'ASSET' || $glBalance->acct_type == 'EXPENSE') {
                    $glBalance->update([
                        "balance" => $glBalance->balance + $value['tran_amount'],
                        'updated_on' => Carbon::now(),
                    ]);
                    $glBalance->update([
                        "balance" => $glBalance->balance - $amount,
                        'updated_on' => Carbon::now(),
                    ]);
                } else {
                    $glBalance->update([
                        "balance" => $glBalance->balance - $value['tran_amount'],
                        'updated_on' => Carbon::now(),
                    ]);

                    $glBalance->update([
                        "balance" => $glBalance->balance + $amount,
                        'updated_on' => Carbon::now(),
                    ]);
                }
            }
            $editHistory = GlHistory::find($value['id']);
            $editHistory->update([
                'tran_amount' => $amount
            ]);
        }
    }


//     public function updateGlHistory(int $tranId, float $amount): void
// {
//     try {
//         DB::transaction(function () use ($tranId, $amount) {
//             $glHistories = GlHistory::where('tran_id', $tranId)->get();

//             foreach ($glHistories as $history) {
//                 $glBalance = GlBalances::where('acct_no', $history->acct_no)->first();

//                 if (!$glBalance) {
//                     throw new \Exception("GL Balance not found for account: {$history->acct_no}");
//                 }

//                 // Determine the sign change based on account type and Dr/Cr indicator
//                 $multiplier = ($glBalance->acct_type == 'ASSET' || $glBalance->acct_type == 'EXPENSE') ? 1 : -1;
//                 $adjustment = ($history->dr_cr_ind == 'Dr') ? -1 : 1;

//                 // Compute new balance
//                 $newBalance = $glBalance->balance + ($adjustment * $multiplier * -$history->tran_amount);
//                 $newBalance += ($adjustment * $multiplier * $amount);

//                 // Update GL balance in a single query
//                 $glBalance->update([
//                     'balance' => $newBalance,
//                     'updated_on' => Carbon::now(),
//                 ]);

//                 // Update GL history
//                 $history->update([
//                     'tran_amount' => $amount
//                 ]);
//             }
//         });
//     } catch (\Exception $e) {
//         Log::error("Failed to update GL History: " . $e->getMessage());
//         throw new \Exception("An error occurred while updating GL history.", 500, $e);
//     }
// }
}
