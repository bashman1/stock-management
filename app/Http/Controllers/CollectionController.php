<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\InstitutionConfig;
use App\Models\Member;
use App\Models\TempCollection;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\CollectionHistory;
use App\Models\SavingsAccount;
use Illuminate\Support\Facades\DB;
use App\Services\CommissionConfigService;
use App\Models\ComissionConfig;

class CollectionController extends Controller
{

    protected $commissionConfig;

    public function __construct(CommissionConfigService $commissionConfig){
        $this->commissionConfig = $commissionConfig;
    }

    public function fieldCollect(Request  $request)
    {
        $userData = auth()->user();
        $tranIdRef = InstitutionConfig::where("type", "tran_id_ref")->first();
        $tranId = $tranIdRef->prefix . '' . $tranIdRef->starting . '' . $tranIdRef->current;
        $tranIdRef->current = $tranIdRef->current + $tranIdRef->step;
        $tranIdRef->save();

        $collect = new TempCollection();
        $collect->amount = $request->amount;
        $collect->description = $request->description;
        $collect->tran_date = $request->tran_date;
        $collect->member_number = $request->member_number;
        $collect->member_id = $request->member_id;
        $collect->institution_id = $userData->institution_id;
        $collect->branch_id = $userData->branch_id;
        $collect->user_id = $userData->id;
        $collect->tran_id = $tranId;
        $collect->status = "Pending";
        $collect->created_by = $userData->id;
        $collect->created_on = now();
        $collect->save();

        return $this->genericResponse(true, "Collected successfully", 201, $collect);
    }

    public function getOfficerCollection()
    {
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();

        $queryString = "SELECT T.*, CONCAT(M.first_name,' ',M.last_name, ' ',M.other_name) AS member_name,
        I.name AS institution_name, B.name AS branch_name,
        CONCAT(U.first_name,' ',U.last_name, ' ',U.other_name) AS officer_name
        FROM temp_collections T
        INNER JOIN members M ON T.member_id = M.id
        INNER JOIN institutions I ON I.id = T.institution_id
        INNER JOIN branches B ON B.id = T.branch_id
        INNER JOIN users U ON T.user_id = U.id";

        if ($isNotAdmin) {
            $queryString .= " WHERE T.institution_id = $userData->institution_id AND T.branch_id = $userData->branch_id AND T.user_id = $userData->id";
        }

        $queryString .= " ORDER BY T.id DESC";

        $transaction = DB::select($queryString);

        return $this->genericResponse(true, "Collected successfully", 201, $transaction);
    }


    public function getReceiptData($tranId)
    {
        $receiptData = DB::select("SELECT T.id, T.amount, T.description, T.tran_date, T.member_number, T.member_id,
            T.institution_id, T.branch_id, T.user_id, T.tran_id, T.status, T.created_by, T.created_at,
            I.name AS institution_name, I.ref_no, B.name AS branch_name,
            CONCAT(M.first_name,' ',M.last_name,' ', M.other_name) AS member_name,
            CONCAT(U.first_name,' ',U.last_name,' ', U.other_name) AS user_name
            FROM temp_collections T INNER JOIN institutions I ON T.institution_id=I.id
            INNER JOIN branches B ON T.branch_id = B.id
            INNER JOIN members M ON T.member_id = M.id
            INNER JOIN users U ON U.id=T.user_id
            WHERE T.tran_id='$tranId'");
        if (!empty($receiptData)) {
            $receiptData = $receiptData[0];
        }
        return $this->genericResponse(true, "Receipt retrieved successfully", 200, $receiptData);
    }


    public function getPendingTransactions($status)
    {
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();

        $queryString= "SELECT T.*, CONCAT(M.first_name,' ',M.last_name, ' ',M.other_name) AS member_name,
        I.name AS institution_name, B.name AS branch_name,
        CONCAT(U.first_name,' ',U.last_name, ' ',U.other_name) AS officer_name
        FROM temp_collections T INNER JOIN members M ON T.member_id = M.id
        INNER JOIN institutions I ON I.id = T.institution_id
        INNER JOIN branches B ON B.id = T.branch_id
        INNER JOIN users U ON T.user_id = U.id";

        if($isNotAdmin){
            $queryString.=" WHERE T.institution_id = $userData->institution_id AND T.branch_id=$userData->branch_id AND T.status='$status'";
        }else{
            $queryString.=" WHERE T.status='$status'";
        }
        $queryString.=" ORDER BY T.id DESC";
        $transactions = DB::select($queryString);
        return $this->genericResponse(true, "Collected successfully", 201, $transactions);
    }


    public function approveTransactions(Request $request)
    {
        try {
            DB::beginTransaction();
            $userData = auth()->user();
            if ($request->action == "Approved") {
                foreach ($request->tranList as  $value) {
                    $approveTmpTran = TempCollection::find($value["id"]);
                    $approveTmpTran->status = $request->action;
                    $approveTmpTran->save();

                    /** approving the transactions **/
                    $collection =  new Collection();
                    $collection->amount = $approveTmpTran->amount;
                    $collection->description = $approveTmpTran->description;
                    $collection->tran_date = $approveTmpTran->tran_date;
                    $collection->member_number = $approveTmpTran->member_number;
                    $collection->member_id = $approveTmpTran->member_id;
                    $collection->institution_id = $approveTmpTran->institution_id;
                    $collection->branch_id = $approveTmpTran->branch_id;
                    $collection->user_id = $userData->id;
                    $collection->tran_id = $approveTmpTran->tran_id;
                    $collection->status = "Active";
                    // $collection->tran_cd = $approveTmpTran->tran_cd;
                    // $collection->tran_indicator = $approveTmpTran->tran_indicator;
                    $collection->temp_collection_id = $approveTmpTran->id;
                    $collection->created_by = $userData->id;
                    $collection->created_on = now();
                    $collection->save();

                    /** Collections history **/
                    $history = new CollectionHistory();
                    $history->amount = $approveTmpTran->amount;
                    $history->description = $approveTmpTran->description;
                    $history->member_number = $approveTmpTran->member_number;
                    $history->member_id = $approveTmpTran->member_id;
                    $history->tran_id = $approveTmpTran->tran_id;
                    $history->tran_cd = $approveTmpTran->tran_cd;
                    $history->tran_indicator = $approveTmpTran->tran_indicator;
                    $history->institution_id = $approveTmpTran->institution_id;
                    $history->branch_id = $approveTmpTran->branch_id;
                    $history->user_id = $userData->id;
                    $history->collection_id = $collection->id;
                    $history->status = "Active";
                    $history->created_by = $userData->id;
                    $history->created_on = now();
                    $history->save();

                    /** Update saving account **/
                    $savingsAcct = SavingsAccount::where("member_number", $approveTmpTran->member_number)->first();
                    if (isset($savingsAcct->id)) {
                        $savingsAcct->balance = $savingsAcct->balance + $approveTmpTran->amount;
                        $savingsAcct->save();
                    }

                    /** collecting the commissions */
                    // commission config
                    $config = ComissionConfig::where(["institution_id"=>$approveTmpTran->institution_id, "branch_id"=>$approveTmpTran->branch_id, "status"=>"Active"])->first();
                    $commissionRequest=(object)[
                        "tran_id"=> $approveTmpTran->tran_id,
                        "amount"=> $config->amount,
                        "commission_config_id"=> $config->id ,
                        "branch_id"=> $approveTmpTran->branch_id ,
                        "institution_id"=>$approveTmpTran->institution_id,
                        "user_id"=>$userData->id ,
                        "status"=> "Pending",
                        "created_by"=> $userData->id,
                        "created_on"=>now()
                    ];
                    $this->commissionConfig->collectCommission($commissionRequest);
                }
            } else {
                foreach ($request->tranList as  $value) {
                    $declineTmpTran = TempCollection::find($value["id"]);
                    $declineTmpTran->status = $request->action;
                    $declineTmpTran->save();
                }
            }
            DB::commit();
            return $this->genericResponse(true, "Transaction $request->action successfully", 201, []);
        } catch (\Throwable $th) {
            // throw new $th;
            return $this->genericResponse(false, "Transaction $request->action failed", 400, $th);
        }
    }


    public function getApprovedTransactions()
    {
        try {
            $userData = auth()->user();
            $isNotAdmin = $this->isNotAdmin();
            $queryString="SELECT T.*, CONCAT(M.first_name,' ',M.last_name, ' ',M.other_name) AS member_name,
            I.name AS institution_name, B.name AS branch_name,
            CONCAT(U.first_name,' ',U.last_name, ' ',U.other_name) AS officer_name
            FROM collections T INNER JOIN members M ON T.member_id = M.id
            INNER JOIN institutions I ON I.id = T.institution_id
            INNER JOIN branches B ON B.id = T.branch_id
            INNER JOIN users U ON T.user_id = U.id";
            if($isNotAdmin){
                $queryString.=" WHERE T.institution_id = $userData->institution_id AND T.branch_id=$userData->branch_id ";
            }
            $queryString.=" ORDER BY T.id DESC";
            $transaction = DB::select($queryString);

            return $this->genericResponse(true, "Approved transactions", 200, $transaction);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->genericResponse(false, "Process failed", 400, $th);
        }
    }
}
