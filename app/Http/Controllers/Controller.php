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
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use App\Models\Institution;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\InstitutionContact;
// use Illuminate\Support\Facades\Log;
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
        // Only audit meaningful events — writes, auth, exports and any failure.
        // Routine successful reads are skipped (kills noise + self-logging of the log viewer).
        if ($this->isAuditable($action, $status)) {
            $meta = $this->deriveAuditMeta($action, $code, $status, $data);
            $req = request();

            $log = [
                "uuid" => (string) Str::uuid(),
                "action" => $action,
                "entity_type" => $meta['entity_type'],
                "entity_id" => $meta['entity_id'],
                "ip" => IpRequest::ip(),
                "user_agent" => $req ? substr((string) $req->userAgent(), 0, 255) : null,
                "method" => $req ? $req->method() : null,
                "route" => $req ? substr((string) $req->path(), 0, 255) : null,
                "http_code" => $code,
                "severity" => $meta['severity'],
                "event_category" => $meta['event_category'],
                "request" => $this->sanitizeForAudit($request instanceof Request ? $request->all() : $request),
                "response" => $this->sanitizeForAudit($data),
                "return_status" => $status,
                "return_message" => $message,
                "user_id" => Auth::user() ? Auth::user()->id : null,
                "institution_id" => Auth::user() ? Auth::user()->institution_id : null,
                "branch_id" => Auth::user() ? Auth::user()->branch_id : null,
                "created_on" => now(),
            ];
            $this->setLogs($log);
        }

        return response()->json([
            "status" => $status,
            "code" => $code,
            "message" => $message,
            "data" => $data
        ], $code);
    }

    /**
     * Audit only writes / auth / exports / failures; skip routine successful reads.
     */
    private function isAuditable($action, $status): bool
    {
        $action = (string) $action;
        if ($status === false) {
            return true; // always audit failures
        }
        if (preg_match('/^(download|export)/i', $action)) {
            return true; // exports/downloads are auditable
        }
        // routine reads are not auditable
        return !preg_match('/^(get|view|list|fetch|search)/i', $action);
    }

    /**
     * Recursively redact secrets and cap the size of audit payloads.
     */
    private function sanitizeForAudit($value)
    {
        $redactKeys = [
            'password', 'password_confirmation', 'current_password', 'new_password',
            'token', 'access_token', 'auth_token', 'secret', 'otp', 'pin',
            'receipt', 'image', 'base64', 'src', 'logo',
        ];

        $clean = function ($data) use (&$clean, $redactKeys) {
            if (is_object($data)) {
                $data = json_decode(json_encode($data), true);
            }
            if (!is_array($data)) {
                return $data;
            }
            $out = [];
            foreach ($data as $k => $v) {
                if (is_string($k) && in_array(strtolower($k), $redactKeys, true)) {
                    $out[$k] = '***REDACTED***';
                } else {
                    $out[$k] = (is_array($v) || is_object($v)) ? $clean($v) : $v;
                }
            }
            return $out;
        };

        $cleaned = $clean($value);

        // size cap: store a summary instead of huge blobs
        $encoded = json_encode($cleaned);
        if ($encoded !== false && strlen($encoded) > 8192) {
            return [
                '_truncated' => true,
                'size' => strlen($encoded),
                'keys' => is_array($cleaned) ? array_slice(array_keys($cleaned), 0, 20) : [],
            ];
        }

        return $cleaned;
    }

    /**
     * Derive entity, severity and event category from the action + outcome.
     */
    private function deriveAuditMeta($action, $code, $status, $data): array
    {
        $action = (string) $action;

        // entity type = action with the leading verb stripped (createExpense -> Expense)
        $entityType = preg_replace('/^(create|update|delete|get|restock|pay|approve|archive|reverse|new)/i', '', $action);
        $entityType = $entityType !== '' ? ucfirst($entityType) : null;

        // entity id from a saved model/object response
        $entityId = null;
        if (is_object($data) && isset($data->id)) {
            $entityId = (string) $data->id;
        } elseif (is_array($data) && isset($data['id'])) {
            $entityId = (string) $data['id'];
        }

        // severity
        if ((int) $code >= 500) {
            $severity = 'ERROR';
        } elseif ($status === false || (int) $code >= 400) {
            $severity = 'WARNING';
        } elseif (preg_match('/^(delete|reverse|archive)/i', $action)) {
            $severity = 'CRITICAL';
        } else {
            $severity = 'INFO';
        }

        // event category
        if (preg_match('/(login|logout|logOut)/i', $action)) {
            $category = 'AUTH';
        } elseif (preg_match('/(expense|order|invoice|gl|payable|receivable|pay|restock|stock|sale|product|reconcile)/i', $action)) {
            $category = 'FINANCIAL';
        } elseif (preg_match('/^(download|export)/i', $action)) {
            $category = 'EXPORT';
        } elseif (preg_match('/^(create|update|delete|approve|archive|new)/i', $action)) {
            $category = 'DATA';
        } else {
            $category = 'SYSTEM';
        }

        return [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'severity' => $severity,
            'event_category' => $category,
        ];
    }


    /**
     * get current logged in user data  04 Oct 2023
     * @return
     * @author Bashir <wamulabash1@gmail.com>
     */
    public function getUserLogin()
    {
        return Auth::user();
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
        return Auth::user()->institution_id !== null;
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

            $checkExistingGlAcct = GlAccounts::where('acct_no', $acctNo)->first();
            if (!isset($checkExistingGlAcct)) {
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
            $checkExistingGlBal = GlBalances::where('acct_no', $acctNo)->first();
            if (!isset($checkExistingGlBal)) {
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

        if (!isset($sl)) {
            return false;
        }
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

        if (!isset($pia)) {
            return false;
        }
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

        if (!isset($pil)) {
            return false;
        }
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

        if (!isset($pp)) {
            return false;
        }
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

        if (!isset($pd)) {
            return false;
        }
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

        if (!isset($rin)) {
            return false;
        }
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

        if (!isset($rot)) {
            return false;
        }
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

        if (!isset($ope)) {
            return false;
        }
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
            // 1307001 was reclassified from ASSET to EXPENSE (Cost of Goods Sold)
            ->where('acct_type', 'EXPENSE')
            ->where('gl_no', '1307001')
            ->first();

        if (!isset($gfs)) {
            return false;
        }
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

        if (!isset($tax)) {
            return false;
        }
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

        if (!isset($rcbl)) {
            return false;
        }
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

        if (!isset($pybl)) {
            return false;
        }
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

        // Accumulated Depreciation (contra-asset) for the asset module
        $accDep = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'ASSET')
            ->where('gl_no', '1113001')
            ->first();
        if (!isset($accDep)) {
            return false;
        }
        $isAdExisting = CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($accDep->replaced_acct_no)])->exists();
        if (!$isAdExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Accumulated Depreciation AD";
            $newSti->param_cd = "AD";
            $newSti->param_value = strrev($accDep->replaced_acct_no);
            $newSti->institution_id = $instId;
            $newSti->created_on = now();
            $newSti->save();
        }

        // Depreciation Expense for the asset module
        $depExp = DB::table('gl_accounts')
            ->selectRaw("REPLACE(REVERSE(acct_no), SUBSTR(REVERSE(acct_no), 17, 3), '***') AS replaced_acct_no")
            ->where('institution_id', $instId)
            ->where('acct_type', 'EXPENSE')
            ->where('gl_no', '5250001')
            ->first();
        if (!isset($depExp)) {
            return false;
        }
        $isDexExisting = CntrlParameter::where(["institution_id" => $instId, "param_value" => strrev($depExp->replaced_acct_no)])->exists();
        if (!$isDexExisting) {
            $newSti = new CntrlParameter();
            $newSti->param_name = "Depreciation Expense DEX";
            $newSti->param_cd = "DEX";
            $newSti->param_value = strrev($depExp->replaced_acct_no);
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
    /**
     * Fetch the gl_balances row for an account, creating a zero-balance row if
     * one does not yet exist (incomplete setup). Throws a descriptive exception
     * if the GL account itself is missing, so the caller's transaction rolls back
     * instead of crashing on a null dereference.
     */
    private function resolveGlBalance($acctNo): GlBalances
    {
        $glBalances = GlBalances::where('acct_no', $acctNo)->first();
        if (isset($glBalances)) {
            return $glBalances;
        }

        $glAccount = GlAccounts::where('acct_no', $acctNo)->first();
        if (!isset($glAccount)) {
            throw new \RuntimeException("GL account $acctNo not found; cannot post to its balance.");
        }

        $glBalances = new GlBalances();
        $glBalances->acct_no = $glAccount->acct_no;
        $glBalances->acct_type = $glAccount->acct_type;
        $glBalances->balance = 0;
        $glBalances->branch_cd = $glAccount->branch_cd;
        $glBalances->status = $glAccount->status;
        $glBalances->institution_id = $glAccount->institution_id;
        $glBalances->branch_id = $glAccount->branch_id;
        $glBalances->created_on = now();
        $glBalances->save();

        return $glBalances;
    }

    public function postGlDR($data): GlHistory
    {
        DB::beginTransaction();
        $glBalances = $this->resolveGlBalance($data->acct_no);

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
        $glBalances = $this->resolveGlBalance($data->acct_no);

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
    public function getControlAcctByCode($code): array|string|null
    {
        $cntrl = CntrlParameter::where(["param_cd" => $code, "institution_id" => Auth::user()->institution_id])->first();
        $branch = Branch::find(Auth::user()->branch_id);
        if (!isset($cntrl) || !isset($branch)) {
            return null;
        }
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

    // --- Shared balanced-journal helpers (used by Expense + Asset modules) -----

    /**
     * Map a payment method to the control-parameter code for the credited account.
     */
    protected function payParamCode($method): string
    {
        switch (strtoupper((string) $method)) {
            case 'BANK':
                return 'CGL';
            case 'CREDIT':
                return 'AP';
            case 'CASH':
            default:
                return 'CL';
        }
    }

    /**
     * Resolve a branch GL account (acct_no + acct_type) from a control-parameter code.
     */
    protected function resolveControlAccount($paramCd, $instId, $branchCode)
    {
        $cp = CntrlParameter::where(['param_cd' => $paramCd, 'institution_id' => $instId])->first();
        if (!isset($cp)) {
            return null;
        }
        $acctNo = str_replace('***', $branchCode, $cp->param_value);
        return GlAccounts::where('acct_no', $acctNo)->first();
    }

    /**
     * Post one balanced journal (a single Dr and Cr) plus the transaction record.
     */
    protected function postJournal($drAcctNo, $drType, $crAcctNo, $crType, $amount, $tranId, $desc, $date, $userData, $tranCd, $label): void
    {
        $tran = (array) [
            'acct_no' => $drAcctNo,
            'acct_type' => $drType,
            'contra_acct_no' => $crAcctNo,
            'contra_acct_type' => $crType,
            'description' => $desc,
            'dr_cr_ind' => 'DR/CR',
            'tran_amount' => $amount,
            'reversal_flag' => 'N',
            'tran_date' => $date,
            'tran_cd' => $tranCd,
            'tran_id' => $tranId,
            'status' => 'Active',
            'institution_id' => $userData->institution_id,
            'branch_id' => $userData->branch_id,
            'created_by' => $userData->id,
            'created_on' => now(),
            'ext_ref' => $drAcctNo,
            'tran' => $label,
        ];
        $this->postTransaction($tran);

        $base = [
            'tran_amt' => $amount,
            'reversal_flag' => 'N',
            'description' => $desc,
            'transaction_date' => $date,
            'tran_type' => $label,
            'tran_cd' => $tranCd,
            'tran_id' => $tranId,
            'institution_id' => $userData->institution_id,
            'branch_id' => $userData->branch_id,
            'created_by' => $userData->id,
            'status' => 'Active',
        ];

        $this->postGlDR((object) array_merge($base, [
            'acct_no' => $drAcctNo, 'acct_type' => $drType,
            'contra_acct_no' => $crAcctNo, 'contra_acct_type' => $crType,
        ]));
        $this->postGlCR((object) array_merge($base, [
            'acct_no' => $crAcctNo, 'acct_type' => $crType,
            'contra_acct_no' => $drAcctNo, 'contra_acct_type' => $drType,
        ]));
    }


    public function getUsersByInstitution($institutionId): array
    {
        $userData = Auth::user();
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
                        WHERE H.acct_no = :acctNo
                        AND COALESCE(H.reversal_flag, 'N') = 'N'";

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

    /**
     * Signed GL balance for an account cumulatively up to a date.
     * Uses the same Dr/Cr normal-balance logic as getGlBalancesAsOf, but as a
     * running balance "as of" $asOfDate (needed for opening/closing stock).
     *
     * @param string $acctNo
     * @param string $asOfDate
     * @param bool   $inclusive  true => transaction_date <= date, false => < date
     * @return float
     */
    public function getGlBalanceAsOf($acctNo, $asOfDate, $inclusive = true): float
    {
        $operator = $inclusive ? '<=' : '<';

        $queryString = "SELECT H.tran_amount, H.dr_cr_ind, B.acct_type
                        FROM gl_histories H
                        INNER JOIN gl_balances B ON H.acct_no = B.acct_no
                        WHERE H.acct_no = :acctNo
                        AND COALESCE(H.reversal_flag, 'N') = 'N'
                        AND H.transaction_date::date $operator :asOfDate
                        ORDER BY H.id ASC";

        $glHistory = DB::select($queryString, ['acctNo' => $acctNo, 'asOfDate' => $asOfDate]);

        $sum = 0;
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
    }


    public function createInstitutionDefaultRole($institutionId)
    {
        try {
            DB::beginTransaction();
            $index = 0;
            $userRole = null;
            $defaultRole = DefaultRole::where('status', "Active")->get();
            foreach ($defaultRole as $value) {
                // Log::info('This is the default role ---.');

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
                    // Log::info('This is an the default permission ++++.');
                    $rolePermission = RolePermission::create([
                        "role_id" => $role->id,
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
        try {
            DB::beginTransaction();
            // $user = $this->createUser($request);

            if (User::where('email', $request->email)->exists()) {
                throw new \Exception('Email already exists', 409);
            }

            $pic = null;
            $sign = null;
            if (isset($request->pic)) {
                $pic = $this->saveBase64Image($request->pic, 'users');
            }
            if (isset($request->sign)) {
                $sign = $this->saveBase64Image($request->sign, 'signatures');
            }



            $user = null;
            if ($request && isset($request->id) && !empty($request->id)) {
                $user = User::find($request->id);
                $user->updated_at = Carbon::now();
            } else {
                $user = new User();
                $user->created_by = Auth::user()->id ?? 1;
                $user->created_on = now();
                $user->password = bcrypt($request->password);
            }

            $user->first_name = $request->first_name;
            $user->last_name = $request->last_name;
            $user->other_name = $request->other_name;
            $user->image = $pic;
            $user->signature = $sign;
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

            if (isset($user->institution_id)) {
                $institution = Institution::find($request->institution_id);
                $this->sendEmailToUser($user->email, $request->password, $institution->name, $user->first_name . ' ' . $user->last_name . ' ' . $user->other_name);
            }



            $user->save();
            // return $user;
            DB::commit();
            return $user;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    public function sendEmailToUser($email,  $temporary_password, $institution_name, $user_name): void
    {
        $body = "
        <p>Dear $user_name,</p>

        <p>Welcome to $institution_name! Your account has been successfully created.</p>

        <p>Here are your login details:</p>

        <p>Username: $email</p>
        <p>Temporary Password: $temporary_password</p>

        <p>For security reasons, we recommend that you log in and change your password immediately after your first login.</p>

        <p>You can access your account by visiting:</p>
        👉<p> [Login URL] </p>";
        $sendMail = [
            "subject" => 'User account creation',
            "body" => $body,
            "has_attachment" => false,
            "to" => $email,
            "cc" => [],
            "bcc" => [],
            "attachment" => "",
            "created_on" => Carbon::now(),
            "attachment_name" => "",
        ];
        // Send the email
        $this->sendMail($sendMail);
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

            $transaction['old_amount'] = $oldAmount;
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
        $glHistory = GlHistory::where(['tran_id' => $tranId, 'tran_amount' => $old_amount])->get();

        // Log::info("~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~");
        // Log::info($glHistory);
        // Log::info($tranId);
        // Log::info($amount);


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


    /**
     * Summary of paginate
     * @param mixed $rowsPerPage
     * @param mixed $totalSql
     * @param mixed $sqlQuery
     * @return LengthAwarePaginator<TKey, mixed>
     */
    public function paginate($rowsPerPage, $totalSql, $sqlQuery, $currentPage = null): LengthAwarePaginator
    {
        try {
            if (is_null($currentPage)) {
                $currentPage = LengthAwarePaginator::resolveCurrentPage();
            }

            $totalResult = DB::select($totalSql);


            $total = isset($totalResult[0]) ? (int) $totalResult[0]->total : 0;

            if (!isset($rowsPerPage)) {
                $rowsPerPage = 10;
            }

            $offset = ($currentPage - 1) * $rowsPerPage;

            $sqlQuery .= " LIMIT $rowsPerPage OFFSET $offset ";

            $results = DB::select($sqlQuery);


            $paginatedLogs = new LengthAwarePaginator(
                $results,
                $total,
                $rowsPerPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return $paginatedLogs;
        } catch (\Throwable $th) {
            throw $th;
        }
    }



    public function getLicenseDetails($institutionId)
    {
        try {
            $queryString = "SELECT L.id, L.license_package_id, L.license_package_price_id,
            L.start_date, L.end_date, L.quantity, L.paid, L.institution_id,
            L.status, L.created_at, P.name AS package_name, P.implementation,
            P.description,X.amount, D.name AS duration, D.unit,
            D.description as duration_desc,
            COALESCE(
                json_agg(
                DISTINCT jsonb_build_object(
                    'id', C.id,
                    'license_benefit_id', C.license_benefit_id,
                    'benefit', B.name,
                    'status', C.status,
                    'created_at', C.created_at
                )
                ) FILTER (WHERE C.id IS NOT NULL),
                '[]'
            ) AS benefits,
                GREATEST(AGE(L.end_date, NOW()), INTERVAL '0') AS remaining_time
            FROM institution_licenses L
            INNER JOIN license_packages P ON P.id =  L.license_package_id
            INNER JOIN license_package_prices X ON X.id = L.license_package_price_id
            INNER JOIN durations D ON D.id = X.duration_id
            LEFT JOIN license_package_benefits C ON C.license_package_id = P.id
            LEFT JOIN license_benefits B ON B.id = C.license_benefit_id
            WHERE L.institution_id = ?
            GROUP BY L.id, P.name, P.implementation, P.description,
            X.amount, D.name, D.unit, D.description";

            return  DB::select($queryString, [$institutionId]);
        } catch (\Throwable $th) {
            throw new \Exception("An error occurred while validating the institution ID.", 500, $th);
        }
    }


    function saveBase64Image(string $base64Image, string $directory = 'images'): ?string
    {
        // Check if base64 string has the image data and mime type
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Image, $type)) {
            $image = substr($base64Image, strpos($base64Image, ',') + 1);
            $extension = strtolower($type[1]); // jpg, png, gif

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                throw new \Exception('Invalid image type');
            }

            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('Base64 decode failed');
            }

            // $fileName = Str::uuid() . '.' . $extension;
            $fileName = now()->format('Y_m_d_His') . '_' . Str::uuid() . '.' . $extension;
            $filePath = $directory . '/' . $fileName;

            Storage::disk('public')->put($filePath, $image);

            // return $filePath; // saved under storage/app/public/$directory
            return asset(Storage::url($filePath));
        }

        return null;
    }



    /**
     * Summary of getCntParameterByCd
     * getting control parameter by code
     * @param mixed $param_cd
     * @param mixed $institution_id
     * @param mixed $branch_cd
     * @return GlAccounts|null
     */
    public function getCntParameterByCd($param_cd, $institution_id, $branch_cd)
    {
        $cnt = CntrlParameter::where(['param_cd' => $param_cd, 'institution_id' => $institution_id])->first();
        if (!isset($cnt)) {
            return null;
        }
        $cntGl = str_replace('***', $branch_cd, $cnt->param_value);
        $acct = GlAccounts::where('acct_no', $cntGl)->first();
        return $acct;
    }




    public function downloadPDF($title, $orientation = 'portrait', $genericData)
    {
        $userData = $this->getUserLogin();
        $institution = Institution::find($userData->institution_id);
        $institutionContact = InstitutionContact::where("institution_id", $userData->institution_id)->first();

        if (count($genericData) > 0) {
            // Set options and load the view
            Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);

            $pdf = Pdf::loadView('generic.report', compact('genericData'));

            // Set paper size and orientation
            $pdf->setBasePath(public_path());
            $pdf->setPaper('A4', $orientation); // 'portrait' or 'landscape'

            // Render the PDF to access the DomPDF canvas
            $pdf->render();
            $dompdf = $pdf->getDomPDF();
            $canvas = $dompdf->getCanvas();
            $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');

            // Add header to each page
            // $canvas->page_text(30, 30, "Document NameContact Information Address", $font, 10, [0, 0, 0]);
            $canvas->page_text(350, 15, $title, $font, 12, [0, 0, 0]);

            $canvas->page_text(30, 10, $institution->name, $font, 10, [0, 0, 0]);
            $canvas->page_text(30, 20, "Phone number:" . $institutionContact->phone_number . " Email:" . $institutionContact->email, $font, 10, [0, 0, 0]);
            $canvas->page_text(30, 30, $institution->address, $font, 10, [0, 0, 0]);

            // Add footer to each page
            // Position and add printed by and powered by text
            $canvas->page_text(30, 550, "Powered by:Smart Collect. Printed by:" . $userData->first_name . ". " . $userData->last_name . " " . $userData->other_name . " On:" . date('D d M Y'), $font, 10, [0, 0, 0]);

            $canvas->page_text(700, 550, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 10, [0, 0, 0]);     //landscape

            // Stream the generated PDF
            return $pdf->stream('sales_report.pdf');
        }
    }

    public function getUserInstitution()
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();
            if (!$isNotAdmin) {
                throw new \Exception("Unauthorized access to institution data.", 403);
            }
            $institution = Institution::find($userData->institution_id);
            return $institution;
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    /**
     * Safe Date
     * This method is used to safely parse a date string and return null if the date is invalid.
     * It also handles bad JS date: "Thu Jan 01 1970 03:00:00 GMT..."
     *
     * @param string|null $value
     * @return string|null
     * @throws \Exception
     * @author Bashir <wamulabash1@gmail.com.com>
     */
    public function safeDate(?string $value): ?string
    {
        if (empty($value) || $value === 'null') {
            return null;
        }

        try {
            // Try normal parsing first (ISO, Y-m-d, etc.)
            $date = Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            try {
                // Handle bad JS date: "Thu Jan 01 1970 03:00:00 GMT..."
                $clean = substr($value, 0, 24);
                $date = Carbon::createFromFormat('D M d Y H:i:s', $clean)
                    ->toDateString();
            } catch (\Throwable $e) {
                return null; // NEVER throw
            }
        }

        // Kill the epoch date here
        return $date === '1970-01-01' ? null : $date;
    }

    /**
     * Keep the warehouse stock sub-ledger in step with branch stock movements.
     * Applies a signed base-unit delta to a product's row in the branch's default
     * warehouse (or the given warehouse). Best-effort: any failure is logged, not
     * thrown, so it can never break a sale or product save.
     */
    protected function adjustWarehouseStock($productId, $branchId, $deltaBaseQty, $warehouseId = null)
    {
        try {
            if (!$warehouseId) {
                $wh = \App\Models\Warehouse::where(['branch_id' => $branchId, 'is_default' => true])->first()
                    ?: \App\Models\Warehouse::where('branch_id', $branchId)->first();
                if (!$wh) {
                    return; // no warehouse for this branch yet
                }
                $warehouseId = $wh->id;
            } else {
                $wh = \App\Models\Warehouse::find($warehouseId);
            }

            $row = \App\Models\WarehouseStock::where([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
            ])->whereNull('location_id')->first();

            if (!$row) {
                $row = new \App\Models\WarehouseStock();
                $row->warehouse_id = $warehouseId;
                $row->location_id = null;
                $row->product_id = $productId;
                $row->quantity = 0;
                $row->institution_id = $wh->institution_id ?? null;
                $row->branch_id = $branchId;
                $row->status = 'Active';
                $row->created_on = now();
            }
            $row->quantity = (float) $row->quantity + (float) $deltaBaseQty;
            $row->updated_on = now();
            $row->save();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('adjustWarehouseStock failed: ' . $e->getMessage());
        }
    }
}
