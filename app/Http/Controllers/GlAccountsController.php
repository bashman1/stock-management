<?php

namespace App\Http\Controllers;

use App\Models\CntrlParameter;
use App\Models\GlAccounts;
use App\Models\GlCat;
use App\Models\GlHierarchy;
use App\Models\GlHistory;
use App\Models\GlSubCat;
use App\Models\GlType;
use App\Models\GlGenerateAccount;
use App\Models\Branch;
use App\Models\GlBalances;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GlAccountsController extends Controller
{


    public function getChartOfAccounts(Request $request)
    {

        $userData = $this->getUserLogin();
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
                   B.name AS branch_name, COALESCE(K.balance, 0) AS balance
                   FROM gl_accounts G
                   INNER JOIN gl_types T ON G.gl_type_no = T.gl_no
                   INNER JOIN gl_sub_cats S ON S.gl_no = G.gl_sub_cat_no
                   INNER JOIN gl_cats C ON C.gl_no= G.gl_cat_no
                   INNER JOIN institutions I ON I.id = G.institution_id
                   INNER JOIN branches B ON B.id = G.branch_id
                   LEFT JOIN gl_balances K ON K.acct_no = G.acct_no ";
                    $queryString .= " WHERE G.gl_type_no=  '" . $glType->gl_no . "' ";
                    if ($isNotAdmin) {
                        $queryString .= " AND G.institution_id = $userData->institution_id  ";
                    }
                    $queryString .= " ORDER BY G.gl_no ASC ";
                    $glAccounts = DB::select($queryString);
                    $typeData['accts'] = $glAccounts;
                    $subCatData['types'][] = $typeData;
                }
                $catData['subCats'][] = $subCatData;
            }
            $chartOfAccounts[] = $catData;
        }
        return $this->genericResponse(true, "Chart of accounts fetched successfully", 201, $chartOfAccounts, "getChartOfAccounts", $request);
    }


    public function getLedgerCat(Request $request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();

        $conditions = array();
        if (isset($request->acct_type)) {
            $conditions['acct_type'] = $request->acct_type;
        }
        if (isset($request->gl_no)) {
            $conditions['gl_no'] = $request->gl_no;
        }
        if (isset($request->description)) {
            $conditions['description'] = ["ilike %'" . $request->description . "'%"];
        }

        $glCat = DB::table('gl_cats')->select('*')->where($conditions)->get();
        return $this->genericResponse(true, "Ledger category fetched successfully", 200, $glCat, "getLedgerCat", $request);
    }


    public function getLedgerSubCat(Request $request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();

        $conditions = array();
        if (isset($request->acct_type)) {
            $conditions['acct_type'] = $request->acct_type;
        }
        if (isset($request->gl_no)) {
            $conditions['gl_no'] = $request->gl_no;
        }
        if (isset($request->gl_cat_no)) {
            $conditions['gl_cat_no'] = $request->gl_cat_no;
        }
        if (isset($request->description)) {
            $conditions['description'] = ["ilike %'" . $request->description . "'%"];
        }
        $glSubCat = DB::table('gl_sub_cats')->select('*')->where($conditions)->get();
        return $this->genericResponse(true, "Ledger sub category fetched successfully", 200, $glSubCat, "getLedgerSubCat", $request);
    }

    public function getLedgerType(Request $request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();

        $conditions = array();
        if (isset($request->acct_type)) {
            $conditions['acct_type'] = $request->acct_type;
        }
        if (isset($request->gl_no)) {
            $conditions['gl_no'] = $request->gl_no;
        }
        if (isset($request->gl_sub_cat_no)) {
            $conditions['gl_sub_cat_no'] = $request->gl_sub_cat_no;
        }
        if (isset($request->gl_cat_no)) {
            $conditions['gl_cat_no'] = $request->gl_cat_no;
        }
        if (isset($request->description)) {
            $conditions['description'] = ["ilike %'" . $request->description . "'%"];
        }

        $glType = DB::table('gl_types')->select('*')->where($conditions)->get();
        return $this->genericResponse(true, "Ledger Types fetched successfully", 200, $glType, "getLedgerType", $request);
    }

    public function glAccounts()
    {
        $userData = $this->getUserLogin();
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
        if ($isNotAdmin) {
            $queryString .= " WHERE G.institution_id = $userData->institution_id";
        }
        $queryString .= " ORDER BY G.gl_no ASC ";
        $glAccounts = DB::select($queryString);
        return $this->genericResponse(true, "Chart of accounts fetched successfully", 201, $glAccounts, "glAccounts", []);
    }


    public function searchGl(Request $request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();

        $conditions = array();
        if (isset($request->acct_type)) {
            $conditions['G.acct_type'] = $request->acct_type;
        }
        if (isset($request->gl_no)) {
            $conditions['G.gl_no'] = $request->gl_no;
        }
        if (isset($request->description)) {
            $conditions['G.description'] = [" ILIKE %" . $request->description . "%"];
        }
        if (isset($request->acct_no)) {
            $conditions['G.acct_no'] = $request->acct_no;
        }
        if (isset($request->gl_type_no)) {
            $conditions['G.gl_type_no'] = $request->gl_type_no;
        }
        if (isset($request->gl_sub_cat_no)) {
            $conditions['G.gl_sub_cat_no'] = $request->gl_sub_cat_no;
        }
            if (isset($request->gl_cat_no)) {
            $conditions['G.gl_cat_no'] = $request->gl_cat_no;
        }
        if ($isNotAdmin) {
            $conditions['G.institution_id'] = $userData->institution_id;
        }
        $results = DB::table('gl_accounts as G')
            ->select(
                'G.acct_no',
                'G.gl_no',
                'G.description',
                'G.gl_cat_no',
                'G.gl_sub_cat_no',
                'G.gl_type_no',
                'G.acct_type',
                'G.branch_cd',
                'G.status',
                'G.institution_id',
                'G.branch_id',
                'G.created_by',
                'G.updated_by',
                'G.created_on',
                'G.updated_on',
                'G.created_at',
                'G.updated_at',
                'T.description AS type_desc',
                'S.description AS sub_cat_desc',
                'C.description AS cat_desc',
                'I.name AS institution_name',
                'B.name AS branch_name',
                'K.balance'
            )
            ->join('gl_types as T', 'G.gl_type_no', '=', 'T.gl_no')
            ->join('gl_sub_cats as S', 'S.gl_no', '=', 'G.gl_sub_cat_no')
            ->join('gl_cats as C', 'C.gl_no', '=', 'G.gl_cat_no')
            ->join('institutions as I', 'I.id', '=', 'G.institution_id')
            ->join('branches as B', 'B.id', '=', 'G.branch_id')
            ->join('gl_balances as K', 'K.acct_no', '=', 'G.acct_no')
            ->where($conditions)->get();

        return $this->genericResponse(true, "Chart of accounts fetched successfully", 201, $results, "searchGl", $request);
    }


    public function getLedgerCategories()
    {
        $cat = GlCat::all();
        return $this->genericResponse(true, "Chart of accounts categories fetched successfully", 200, $cat, "getLedgerCategories", []);
    }


    public function updateGlAcct(Request $request)
    {
        $userData = $this->getUserLogin();
        $request->validate([
            'description' => 'required',
            'acctNo' => 'required'
        ]);
        $glAcct = GlAccounts::where('acct_no', $request->acctNo)->first();
        if (!isset($glAcct)) {
            return $this->genericResponse(false, "Ledger account not found", 400, $glAcct, "updateGlAcct", $request);
        }
        $glAcct->description = $request->description;
        $glAcct->updated_by = $userData->id;
        $glAcct->updated_on = now();
        $glAcct->save();

        return $this->genericResponse(true, "Ledger account updated successfully", 201, $glAcct, "updateGlAcct", $request);
    }


    public function debitCreditGl(Request $request)
    {
        $userData = $this->getUserLogin();
        DB::beginTransaction();
        $tran = (array)[
            "acct_no" => $request->drAcctNo,
            "acct_type" => $request->drAcctType,
            "contra_acct_no" => $request->crAcctNo,
            "contra_acct_type" => $request->crAcctType,
            "description" => $request->description,
            "dr_cr_ind" => "DR/CR",
            "tran_amount" => $request->tranAmt,
            "reversal_flag" => "N",
            "tran_date" => $request->tranDate,
            "tran_cd" => "GLI",
            "tran_id" => $this->generateUuid(),
            "status" => $request->status,
            "institution_id" => $userData->institution_id,
            "branch_id" => $userData->branch_id,
            "created_by" => $userData->id,
            "created_on" => now(),
            // "member_id"=>null,
            // "product_id"=>$product->id,
            // "stock_id"=>null,
            "ext_ref" => $request->drAcctNo,
            "tran" => "GL Injection",
            // "reversal_reason"=>null,
            // "reversed_by"=>null,
            // "reversed_on"=>null,
        ];

        $postTran = $this->postTransaction($tran);

        $debitRequest = (object)[
            "acct_no" => $request->drAcctNo,
            "acct_type" => $request->drAcctType,
            "tran_amt" => $request->tranAmt,
            "reversal_flag" => 'N',
            "description" => $request->description,
            "transaction_date" => $request->tranDate,
            "contra_acct_no" => $request->crAcctNo,
            "contra_acct_type" => $request->crAcctType,
            "tran_type" => 'GL INJECTION',
            "tran_cd" => 'GLI',
            "tran_id" => optional($postTran)->tran_id,
            "institution_id" => $userData->institution_id,
            "branch_id" => $userData->branch_id,
            "created_by" => $userData->id,
            "status" => $request->status,
        ];

        $creditRequest = (object)[
            "acct_no" => $request->crAcctNo,
            "acct_type" => $request->crAcctType,
            "tran_amt" => $request->tranAmt,
            "reversal_flag" => 'N',
            "description" => $request->description,
            "transaction_date" => $request->tranDate,
            "contra_acct_no" => $request->drAcctNo,
            "contra_acct_type" => $request->drAcctType,
            "tran_type" => 'GL INJECTION',
            "tran_cd" => 'GLI',
            "tran_id" => optional($postTran)->tran_id,
            "institution_id" => $userData->institution_id,
            "branch_id" => $userData->branch_id,
            "created_by" => $userData->id,
            "status" => $request->status,
        ];
        $debit = $this->postGlDR($debitRequest);
        $credit = $this->postGlCR($creditRequest);
        DB::commit();
        return $this->genericResponse(true, "Transaction posted successfully", 201, ['debit' => $debitRequest, 'credit' => $creditRequest], "debitCreditGl", $request);
    }


    public function createGlAcct(Request $request)
    {
        try {
            DB::beginTransaction();
            $postRequest = (object)[
                "gl_cat_no" => $request->cat,
                "gl_sub_cat_no" => $request->subCat,
                "gl_type_no" => $request->type,
                "status" => $request->status,
                "description" => $request->name,
            ];
            $subAccount = $this->reUsableCreateGlAcct($postRequest);
            DB::commit();
            return $this->genericResponse(true, "Ledger account created successfully", 201, $subAccount, "createGlAcct", $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, "createGlAcct", $request);
        }
    }


    public function glAcctHistory(Request $request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();

        $queryString = "SELECT H.id, H.acct_no,A.acct_type, H.dr_cr_ind, H.tran_amount, H.reversal_flag, H.description, H.transaction_date,
        H.contra_acct_no, H.contra_acct_type, H.tran_type, H.tran_id, H.status, H.institution_id, H.branch_id,
        H.created_by, H.created_on, H.created_at, H.updated_at, CONCAT(U.first_name,' ',U.last_name,' ', U.other_name) AS user_name,
        I.name AS institution_name, B.name AS branch_name
        FROM gl_histories H
        INNER JOIN gl_accounts A ON A.acct_no = H.acct_no
        INNER JOIN institutions I ON I.id = H.institution_id
        INNER JOIN branches B ON B.id = H.branch_id
        INNER JOIN users U ON U.id = H.created_by";

        $queryString .= " WHERE H.acct_no=  '" . $request->acct_no . "' ";

        if ($isNotAdmin) {
            $queryString .= " AND H.institution_id = $userData->institution_id  ";
        }
        $queryString .= " ORDER BY H.transaction_date ASC ";
        $glHistory = DB::select($queryString);
        $balance = 0;
        $tempArray = array();

        $tempArray[] = ['transaction_date' => '2024-03-27 21:00:00', 'description' => 'Balance carried forward', "dr_cr_ind" => '', 'balance' => '0', 'reversal_flag' => 'N', 'user_name' => ''];

        foreach ($glHistory as $value) {
            $newValue = clone $value;
            if ($value->dr_cr_ind == 'Dr') {
                $balance += ($value->acct_type == 'ASSET' || $value->acct_type == 'EXPENSE') ? (float) $value->tran_amount : -(float) $value->tran_amount;
            } elseif ($value->dr_cr_ind == 'Cr') {
                $balance -= ($value->acct_type == 'ASSET' || $value->acct_type == 'EXPENSE') ? (float) $value->tran_amount : -(float) $value->tran_amount;
            }
            $newValue->balance = $balance;
            $tempArray[] = $newValue;
        }
        return $this->genericResponse(true, "Gl accounts history fetched successfully", 201, $tempArray, "glAcctHistory", $request);
    }

    public function glAcctOverView(Request $request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();

        $glCat = GlCat::all();
        $tempArray = [];
        foreach ($glCat as $key => $value) {
            $queryString = "SELECT B.id, B.acct_no, B.acct_type, B.balance, B.branch_cd, B.institution_id, B.branch_id, B.created_on, B.created_at, B.updated_at,
            A.description, A.gl_no
            FROM gl_balances B INNER JOIN gl_accounts A ON B.acct_no = A.acct_no
            WHERE balance > 0 AND B.acct_type= '" . $value['acct_type'] . "'";
            if ($isNotAdmin) {
                $queryString .= " AND B.institution_id = $userData->institution_id  ";
            }
            $queryString .= " ORDER BY B.id ASC ";
            $glBalances = DB::select($queryString);
            if (!empty($glBalances)) {
                $tempArray[] = $glBalances;
            }
        }

        return $this->genericResponse(true, "Gl accounts overview fetched successfully", 201, $tempArray, "glAcctOverView", $request);
    }


    public function getGlBalances(Request $request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();

        $glCat = GlCat::all();
        $tempArray = [];
        foreach ($glCat as $key => $value) {
            $sumSql = "SELECT sum(balance) AS balance, acct_type  FROM gl_balances WHERE acct_type='" . $value['acct_type'] . "'";
            if ($isNotAdmin) {
                $sumSql .= " AND institution_id = $userData->institution_id  ";
            }
            $sumSql .= " GROUP BY acct_type";

            $total = DB::select($sumSql);
            $total = $total[0];

            $queryString = "SELECT B.id, B.acct_no, B.acct_type, B.balance, B.branch_cd, B.institution_id, B.branch_id, B.created_on, B.created_at, B.updated_at,
            A.description, A.gl_no
            FROM gl_balances B INNER JOIN gl_accounts A ON B.acct_no = A.acct_no
            WHERE balance > 0 AND B.acct_type= '" . $value['acct_type'] . "'";
            if ($isNotAdmin) {
                $queryString .= " AND B.institution_id = $userData->institution_id  ";
            }
            $queryString .= " ORDER BY B.id ASC ";
            $glBalances = DB::select($queryString);
            $tempArray[] = $total;
            return $this->genericResponse(true, "Gl accounts balances fetched successfully", 201, $tempArray, "getGlBalances", $request);
        }
    }


    public function getCntrlParamGl()
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();
            $conditions =  array();
            $conditions['status'] = "Active";
            if ($isNotAdmin) {
                $conditions['institution_id'] = $userData->institution_id;
            }
            $cntrlParam = CntrlParameter::where($conditions)->get();
            return $this->genericResponse(true, "Control accounts", 200, $cntrlParam, "getCntrlParamGl", []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 400, $th, "getCntrlParamGl", []);
        }
    }


    public function generateIncomeStatement(Request $request)
    {
        // return $this->genericResponse(true,"",200, ["startDate"=> $request->fromDate, "endDate"=>$request->toDate, "request"=>$request]);
        // try {
        $userData = $this->getUserLogin();
        $sales = CntrlParameter::where(["param_cd" => "SL", "institution_id" => $userData->institution_id])->first();
        $branch = Branch::find($userData->branch_id);
        $salesAcctNo = str_replace("***", $branch->code, $sales->param_value);

        $returnOutwards = CntrlParameter::where(["param_cd" => "ROT", "institution_id" => $userData->institution_id])->first();
        $returnAcctNo = str_replace("***", $branch->code,  $returnOutwards->param_value);

        $openingStock = CntrlParameter::where(["param_cd" => "STI", "institution_id" => $userData->institution_id])->first();
        $stockAcctNo = str_replace("***", $branch->code,  $openingStock->param_value);

        $purchases = CntrlParameter::where(["param_cd" => "PIA", "institution_id" => $userData->institution_id])->first();
        $purchaseAcctNo = str_replace("***", $branch->code,  $purchases->param_value);

        // Operating expenses = every EXPENSE account for this branch EXCEPT the ones
        // already consumed by the gross-profit / COGS / returns logic: COGS/GFS,
        // passage/PP, return-inwards/RIN, return-outwards/ROT. Resolved dynamically
        // from control parameters (not hardcoded) so nothing is double-counted.
        $excludeAcctNos = [];
        foreach (['GFS', 'PP', 'RIN', 'ROT'] as $cd) {
            $cp = CntrlParameter::where(["param_cd" => $cd, "institution_id" => $userData->institution_id])->first();
            if ($cp) {
                $excludeAcctNos[] = str_replace("***", $branch->code, $cp->param_value);
            }
        }

        $opexQuery = DB::table('gl_histories as h')
            ->join('gl_accounts as a', 'a.acct_no', '=', 'h.acct_no')
            ->where('a.acct_type', 'EXPENSE')
            ->where('a.institution_id', $userData->institution_id)
            ->where('a.branch_id', $branch->id)
            ->whereRaw("COALESCE(h.reversal_flag, 'N') = 'N'");
        if (!empty($excludeAcctNos)) {
            $opexQuery->whereNotIn('a.acct_no', $excludeAcctNos);
        }
        if ($request->fromDate && $request->toDate) {
            $opexQuery->whereRaw("h.transaction_date::date BETWEEN ? AND ?", [$request->fromDate, $request->toDate]);
        }
        $operatingExpenses = $opexQuery
            ->groupBy('a.acct_no', 'a.description')
            ->selectRaw("a.acct_no, a.description, SUM(CASE WHEN h.dr_cr_ind = 'Dr' THEN h.tran_amount ELSE -h.tran_amount END) AS amount")
            ->havingRaw("SUM(CASE WHEN h.dr_cr_ind = 'Dr' THEN h.tran_amount ELSE -h.tran_amount END) <> 0")
            ->orderByRaw("amount DESC")
            ->get();

        $passage = CntrlParameter::where(["param_cd" => "PP", "institution_id" => $userData->institution_id])->first();
        $passageAcctNo = str_replace("***", $branch->code,  $passage->param_value);

        // $totalSales = (float) DB::table('gl_histories')
        //     ->where('acct_no', $salesAcctNo)
        //     ->whereBetween('transaction_date', [$request->fromDate, $request->toDate])
        //     ->sum('tran_amount');

        $totalSales = $this->getGlBalancesAsOf($request->fromDate, $request->toDate, $salesAcctNo);

        // $totalReturns = (float) DB::table('gl_histories')
        //     ->where('acct_no', $returnAcctNo)
        //     ->whereBetween('transaction_date', [$request->fromDate, $request->toDate])
        //     ->sum('tran_amount');


        $totalReturns = $this->getGlBalancesAsOf($request->fromDate, $request->toDate, $returnAcctNo);


        // $totalOpenStock = (float) DB::table('gl_histories')
        //     ->where('acct_no', $stockAcctNo)
        //     ->where('transaction_date', '<', $request->fromDate)
        //     ->sum('tran_amount');

        // Opening stock = signed STI balance strictly before the period start
        $totalOpenStock = $this->getGlBalanceAsOf($stockAcctNo, $request->fromDate, false);

        // $totalCloseStock = (double) DB::table('gl_histories')
        //     ->where('acct_no', $stockAcctNo)
        //     ->whereBetween('transaction_date', [$request->fromDate, $request->toDate])
        //     ->sum('tran_amount');

        // $totalPurchases = (float) DB::table('gl_histories')
        //     ->where('acct_no', $purchaseAcctNo)
        //     ->whereBetween('transaction_date', [$request->fromDate, $request->toDate])
        //     ->sum('tran_amount');
        $totalPurchases = $this->getGlBalancesAsOf($request->fromDate, $request->toDate, $purchaseAcctNo);

        // total operating expenses = sum of the per-category breakdown computed above
        $totalOperatingExpenses = (float) $operatingExpenses->sum('amount');

        // $totalPassage = (float) DB::table('gl_histories')
        //     ->where('acct_no', $passageAcctNo)
        //     ->whereBetween('transaction_date', [$request->fromDate, $request->toDate])
        //     ->sum('tran_amount');


        $totalPassage = $this->getGlBalancesAsOf($request->fromDate, $request->toDate, $passageAcctNo);

        // $totalIncome = (float) DB::table('gl_balances')
        //     ->where(['acct_type' => 'INCOME', 'institution_id' => $branch->institution_id, 'branch_id' => $branch->id])
        //     ->sum('balance');

        $getIncomeAccounts = GlAccounts::where(['acct_type' => 'INCOME', 'institution_id' => $branch->institution_id, 'branch_id' => $branch->id])->get();

        $totalIncome = 0;
        foreach ($getIncomeAccounts as $key => $value) {
            $sum = $this->getGlBalancesAsOf($request->fromDate, $request->toDate, $value['acct_no']);
            $totalIncome = $totalIncome + $sum;
        }

        // $totalIncome = $this->getGlBalancesAsOf();

        //return needs to be purchase return
        $goodsAvailableForSale = ($totalOpenStock + (($totalPurchases + $totalPassage) - $totalReturns));


        // closing stock is Opening Stock+Purchases−Sales+Sales Returns−Purchase Returns±Adjustments
        // $totalCloseStock = $totalOpenStock + $totalPurchases - $totalSales;

        // Closing stock = signed STI balance as of the period end
        $totalCloseStock = $this->getGlBalanceAsOf($stockAcctNo, $request->toDate, true);


        // cost of sale = goods available for sale - closing stock
        $costOfSales =  $goodsAvailableForSale - $totalCloseStock;
        $grossProfit =  ($totalSales - $totalReturns) - $costOfSales;


        $netProfit = $grossProfit - $totalOperatingExpenses;

        $profitBeforeInterestAndTax = $totalIncome - $totalOperatingExpenses;

        $response = [
            'totalSales' => $totalSales,
            'totalReturnIn' => $totalReturns,
            'netSales' => $totalSales - $totalReturns,
            'openingStock' => $totalOpenStock,
            'closingStock' => $totalCloseStock,
            'goodAvailableForSale' => $goodsAvailableForSale,
            'costOfSales' => $costOfSales,
            'grossProfit' => $grossProfit,
            'netProfit' => $netProfit,
            'totalPassage' => $totalPassage,
            'totalOperatingExpenses' => $totalOperatingExpenses,
            'operatingExpenses' => $operatingExpenses,
            'totalPurchases' => $totalPurchases,
            'totalIncome' => $totalIncome,
            'profitBeforeInterestAndTax' => $profitBeforeInterestAndTax,
        ];

        return $this->genericResponse(true, 'total sales got', 200, $response, "generateIncomeStatement", $request);
        // } catch (\Throwable $th) {
        //     return $this->genericResponse(false, $th->getMessage(), 400, $th);
        // }
    }


    public function getBalanceSheet(Request $request)
    {
        try {
            // $userData = auth()->user();
            $userData = $this->getUserLogin();
            // A balance sheet is per entity, so always scope to the user's branch.
            $instId = intval($userData->institution_id);
            $branchId = intval($userData->branch_id);
            $tempArray = array();

            // Asset / Liability (and Equity/Capital) sections, grouped by sub-category.
            // All accounts are included — excluding one side of a balanced pair (the
            // old PIA-only exclusion) is what previously unbalanced the sheet.
            $glSubCat = GlSubCat::all();
            foreach ($glSubCat as $key => $value) {
                $queryString = "SELECT A.acct_no, A.gl_no, A.description, A.gl_cat_no,
                A.gl_type_no, A.acct_type, A.status, B.balance
                FROM gl_accounts A INNER JOIN gl_balances B ON A.acct_no = B.acct_no
                WHERE (B.balance > 0 OR B.balance < 0)
                AND A.gl_sub_cat_no = '" . $value['gl_no'] . "'
                AND A.institution_id = $instId AND A.branch_id = $branchId
                ORDER BY A.id ASC ";
                $glList = DB::select($queryString);
                array_push($glList, ['description' => 'Total', 'balance' => null, 'total' => $this->getTotalLedgerBalances($glList)]);
                $value['list'] = $glList;
                if ($value['acct_type'] != 'INCOME' && $value['acct_type'] != 'EXPENSE') {
                    array_push($tempArray, $value);
                }
            }

            // Totals by account type for this branch, used for the equity section and
            // the Assets = Liabilities + Equity balance check.
            $byType = DB::table('gl_balances')
                ->select('acct_type', DB::raw('COALESCE(SUM(balance), 0) as total'))
                ->where('institution_id', $instId)
                ->where('branch_id', $branchId)
                ->groupBy('acct_type')
                ->pluck('total', 'acct_type');

            $totalAssets = (float) ($byType['ASSET'] ?? 0);
            $totalLiabilities = (float) ($byType['LIABILITY'] ?? 0);
            $totalEquity = (float) ($byType['EQUITY'] ?? 0) + (float) ($byType['CAPITAL'] ?? 0);
            $totalIncome = (float) ($byType['INCOME'] ?? 0);
            $totalExpense = (float) ($byType['EXPENSE'] ?? 0);
            // Retained earnings = accumulated net profit (now includes COGS as an expense)
            $retainedEarnings = $totalIncome - $totalExpense;
            $totalEquityAndRetained = $totalEquity + $retainedEarnings;
            $balanceCheck = $totalAssets - ($totalLiabilities + $totalEquityAndRetained);

            $summary = [
                'totalAssets' => $totalAssets,
                'totalLiabilities' => $totalLiabilities,
                'totalEquity' => $totalEquity,
                'retainedEarnings' => $retainedEarnings,
                'totalEquityAndRetained' => $totalEquityAndRetained,
                'totalLiabilitiesAndEquity' => $totalLiabilities + $totalEquityAndRetained,
                'balanceCheck' => $balanceCheck,
                'balanced' => abs($balanceCheck) < 0.01,
            ];

            return $this->genericResponse(true, 'Balance sheet', 200, ['sections' => $tempArray, 'summary' => $summary], "getBalanceSheet", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 400, $th, "getBalanceSheet", $request);
        }
    }


    public function getTotalLedgerBalances($acctList)
    {
        $total = 0;
        foreach ($acctList as $key => $value) {
            $total =  $total + (float) $value->balance;
        }
        return $total;
    }


    public function getCashBook(Request $request)
    {
        $userData = $this->getUserLogin();
        $instId = intval($userData->institution_id);
        $branchId = intval($userData->branch_id);
        $branch = Branch::find($branchId);
        if (!isset($branch)) {
            return $this->genericResponse(false, "Branch not found", 400, null, "getCashBook", $request);
        }

        $from = $request->fromDate ?? null;
        $to = $request->toDate ?? null;

        // Cash (CL), Bank (CGL) and Purchase-Discount (PD) accounts for this branch
        $params = CntrlParameter::where('status', 'Active')
            ->where('institution_id', $instId)
            ->whereIn('param_cd', ['CL', 'CGL', 'PD'])
            ->get();

        $entries = [];
        $acctByCd = [];
        foreach ($params as $p) {
            $acctNo = str_replace('***', $branch->code, $p->param_value);
            $acctByCd[$p->param_cd] = $acctNo;

            $q = GlHistory::where([
                    'acct_no' => $acctNo,
                    'institution_id' => $instId,
                    'branch_id' => $branchId,
                ])
                // exclude reversed / deleted entries to match the rest of the ledger
                ->whereRaw("COALESCE(reversal_flag, 'N') = 'N'")
                ->where(function ($w) {
                    $w->whereNull('status')->orWhere('status', '!=', 'Deleted');
                });
            if ($from) { $q->whereDate('transaction_date', '>=', $from); }
            if ($to)   { $q->whereDate('transaction_date', '<=', $to); }

            foreach ($q->get() as $h) {
                $h->ind = $p->param_cd;
                $entries[] = $h;
            }
        }

        // chronological order across all cash accounts
        usort($entries, fn($a, $b) => strcmp((string) $a->transaction_date, (string) $b->transaction_date));

        // Opening balances (signed, strictly before the period). Cash/Bank are assets,
        // so the opening balance is the net Dr-Cr up to the start date.
        $openingCash = ($from && isset($acctByCd['CL'])) ? $this->getGlBalanceAsOf($acctByCd['CL'], $from, false) : 0;
        $openingBank = ($from && isset($acctByCd['CGL'])) ? $this->getGlBalanceAsOf($acctByCd['CGL'], $from, false) : 0;

        $sum = function ($cd, $dc) use ($entries) {
            $t = 0;
            foreach ($entries as $h) {
                if ($h->ind === $cd && $h->dr_cr_ind === $dc) {
                    $t += (float) $h->tran_amount;
                }
            }
            return $t;
        };

        $cashReceipts = $sum('CL', 'Dr');
        $cashPayments = $sum('CL', 'Cr');
        $bankReceipts = $sum('CGL', 'Dr');
        $bankPayments = $sum('CGL', 'Cr');

        $summary = [
            'openingCash'      => $openingCash,
            'cashReceipts'     => $cashReceipts,
            'cashPayments'     => $cashPayments,
            'closingCash'      => $openingCash + $cashReceipts - $cashPayments,
            'openingBank'      => $openingBank,
            'bankReceipts'     => $bankReceipts,
            'bankPayments'     => $bankPayments,
            'closingBank'      => $openingBank + $bankReceipts - $bankPayments,
            'discountAllowed'  => $sum('PD', 'Dr'),
            'discountReceived' => $sum('PD', 'Cr'),
        ];

        return $this->genericResponse(true, "Cash book fetched successfully", 200, ['entries' => $entries, 'summary' => $summary], "getCashBook", $request);
    }


    public function getVATPayableReport(Request $request)
    {
        try {
            $vatPayable = $this->getVATPayable($request);
            return $this->genericResponse(true, "VAT payable", 200, $vatPayable, "getVATPayableReport", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getVATPayableReport", $request);
        }
    }

    public function getVATPayable($request)
    {
        try {
            // $userData = auth()->user();
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();
            $queryString = "SELECT I.id, I.order_id, I.product_id, I.qty, I.status,
                I.institution_id, I.branch_id, I.created_on, I.created_at,
                COALESCE(I.selling_price, S.selling_price) AS selling_price,
                O.ref_no, O.receipt_no, O.tran_id, O.user_id, O.customer_id,
                P.name, P.product_no, P.ref_no AS product_ref, P.tax_config,
                COALESCE(I.purchase_price, S.purchase_price) AS purchase_price, N.name AS institution_name, N.is_tax_enabled,
                COALESCE(I.purchase_price, S.purchase_price) * I.qty AS total_purchases,
                COALESCE(Y.name, 'Cash') AS customer_name,
                -- Calculate the amount
                COALESCE(I.qty * I.selling_price, I.qty * S.selling_price) AS amount,
                -- Calculate VAT
                CASE
                    WHEN P.tax_config = 'TAX_INCLUSIVE' OR P.tax_config = 'TAX_EXCLUSIVE' THEN
                        COALESCE(0.18 * I.qty * I.selling_price, 0.18 * I.qty * S.selling_price)
                    WHEN P.tax_config = 'TAX_EXEMPTED' THEN 0
                    ELSE 0
                END AS vat,
                -- Calculate the amount after VAT adjustment
                CASE
                    WHEN P.tax_config = 'TAX_INCLUSIVE' THEN
                        COALESCE(I.qty * I.selling_price, I.qty * S.selling_price) -
                        CASE
                            WHEN P.tax_config = 'TAX_INCLUSIVE' OR P.tax_config = 'TAX_EXCLUSIVE' THEN
                                COALESCE(0.18 * I.qty * I.selling_price, 0.18 * I.qty * S.selling_price)
                            WHEN P.tax_config = 'TAX_EXEMPTED' THEN 0
                            ELSE 0
                        END
                    WHEN P.tax_config = 'TAX_EXCLUSIVE' THEN
                        COALESCE(I.qty * I.selling_price, I.qty * S.selling_price) +
                        CASE
                            WHEN P.tax_config = 'TAX_INCLUSIVE' OR P.tax_config = 'TAX_EXCLUSIVE' THEN
                                COALESCE(0.18 * I.qty * I.selling_price, 0.18 * I.qty * S.selling_price)
                            WHEN P.tax_config = 'TAX_EXEMPTED' THEN 0
                            ELSE 0
                        END
                    WHEN P.tax_config = 'TAX_EXEMPTED' THEN 0
                    ELSE COALESCE(I.qty * I.selling_price, I.qty * S.selling_price)
                END AS amount_after_vat
            FROM order_items I
            INNER JOIN orders O ON O.id = I.order_id
            INNER JOIN products P ON P.id = I.product_id
            INNER JOIN stocks S ON S.product_id = I.product_id AND S.branch_id = I.branch_id
            INNER JOIN institutions N ON N.id = I.institution_id
            LEFT JOIN customers Y ON O.customer_id = Y.id ";

            if ($isNotAdmin) {
                $queryString .= " WHERE I.institution_id = $userData->institution_id AND I.branch_id=$userData->branch_id ";
            }
            $queryString .= " ORDER BY I.id DESC ";
            return DB::select($queryString);
            // return $this->genericResponse(true, "VAT payable", 200, $vatPayable);
        } catch (\Throwable $th) {
            // return $th->getMessage();
            return $this->genericResponse(false, $th->getMessage(), 500, null);
        }
    }



    public function createSubAccount(Request $request)
    {
        try {
            DB::beginTransaction();
            // $userData = auth()->user();
            $userData = $this->getUserLogin();
            $glAcct = GlAccounts::where('acct_no', $request->acctNo)->first();
            if (!isset($glAcct)) {
                return $this->genericResponse(false, "Account not found", 404, $glAcct);
            }

            $postRequest = (object)[
                "gl_cat_no" => $glAcct->gl_cat_no,
                "gl_sub_cat_no" => $glAcct->gl_sub_cat_no,
                "gl_type_no" => $glAcct->gl_type_no,
                "status" => $glAcct->status,
                "description" => $request->description,
            ];
            $subAccount = $this->reUsableCreateGlAcct($postRequest);

            $glHierarchy = GlHierarchy::create([
                "acct_no" => $subAccount->acct_no,
                "parent_acct_no" => $request->acctNo,
                "status" => $glAcct->status,
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "created_on" => Carbon::now(),
            ]);

            DB::commit();
            return $this->genericResponse(true, "Ledger account created successfully", 201, $subAccount, "createSubAccount", $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "createSubAccount", $request);
        }
    }


    public function reUsableCreateGlAcct($request): GlAccounts
    {
        try {
            DB::beginTransaction();

            // $userData = auth()->user();
            $userData = $this->getUserLogin();
            $branch = Branch::find($userData->branch_id);

            // Check if GL Generate Account exists
            $genAcct = GlGenerateAccount::where([
                'gl_cat_no' => $request->gl_cat_no,
                'gl_sub_cat_no' => $request->gl_sub_cat_no,
                'gl_type_no' => $request->gl_type_no,
                'status' => $request->status
            ])->firstOrFail();

            // Generate GL Number
            $glNo = $genAcct->const + $genAcct->current + $genAcct->step;
            $genAcct->increment('current', $genAcct->step);

            // Generate new account number
            $newAcctNo = $this->generateGlAcctNo($userData->institution_id, $branch->code, $glNo);

            // Check if account or balance already exists
            if (GlAccounts::where('acct_no', $newAcctNo)->exists() || GlBalances::where('acct_no', $newAcctNo)->exists()) {
                throw new \Exception("Ledger account already exists.");
            }

            // Create new GL Account
            $glAcct = GlAccounts::create([
                'gl_no' => $glNo,
                'acct_no' => $newAcctNo,
                'description' => $request->description,
                'gl_cat_no' => $request->gl_cat_no,
                'gl_sub_cat_no' => $request->gl_sub_cat_no,
                'gl_type_no' => $request->gl_type_no,
                'acct_type' => $genAcct->acct_type,
                'status' => $request->status,
                'branch_cd' => $branch->code,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now()
            ]);

            // Create new GL Balance
            GlBalances::create([
                'acct_no' => $newAcctNo,
                'acct_type' => $genAcct->acct_type,
                'balance' => 0,
                'branch_cd' => $branch->code,
                'status' => $request->status,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now()
            ]);

            DB::commit();
            return $glAcct;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw new \Exception("An unexpected error occurred: " . $th->getMessage());
        }
    }


    public function getGlHierarchy(Request $request)
    {
        try {
            // $userData = auth()->user();
            $userData = $this->getUserLogin();
            $hierarchy = GlHierarchy::where(["institution_id" => $userData->institution_id, "branch_id" => $userData->branch_id, "status" => $request->status])->get();
            return $this->genericResponse(true, "Chart of accounts hierarchy fetched successfully", 200, $hierarchy, "getGlHierarchy", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getGlHierarchy", $request);
        }
    }
}
