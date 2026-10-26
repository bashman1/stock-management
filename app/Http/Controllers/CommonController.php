<?php

namespace App\Http\Controllers;

use App\Models\EfrisOutBound;
use App\Models\Notification;
use App\Models\NotificationType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\EfrisApiService;

class CommonController extends Controller
{

    protected EfrisApiService $efrisService;

    public function __construct(EfrisApiService $efrisService)
    {
        $this->efrisService = $efrisService;
    }

    public function getDashboardStats()
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();

        // Branch users see their own branch; system admins (no institution) see everything.
        // $scope('X') filters a table aliased X; $instScope('X') stops at the institution.
        $institutionId = intval($userData->institution_id);
        $branchId = intval($userData->branch_id);
        $scope = fn ($alias) => $isNotAdmin ? " AND $alias.institution_id = $institutionId AND $alias.branch_id = $branchId" : "";
        $instScope = fn ($alias) => $isNotAdmin ? " AND $alias.institution_id = $institutionId" : "";
        // "today" in the app timezone, not the database server's
        $today = now()->toDateString();

        // Control accounts (STI, SL, ...) are per-branch: the parameter holds "***"
        // where the branch code goes. Resolve them for every branch in scope.
        $branchFilter = $isNotAdmin ? " AND BR.id = $branchId" : "";
        $controlAccts = fn ($codes) => "SELECT REPLACE(C.param_value, '***', BR.code) FROM cntrl_parameters C
            INNER JOIN branches BR ON BR.institution_id = C.institution_id
            WHERE C.param_cd IN ($codes) $branchFilter";
        $inventoryAccts = $controlAccts("'STI'");
        $revenueAccts = $controlAccts("'SL', 'SVR', 'DGS'");
        // cost of goods sold and returns inwards are trading costs, not expenses
        $tradingCostAccts = $controlAccts("'GFS', 'RIN'");
        // "Income" matches the income statement's Total income: all income accounts
        // less cost of sales and returns (gross profit + other income). Plain income
        // was just sales again for a shop. Purchases are stock receipts only (STI),
        // not adjustments or sale reversals that also debit inventory.
        // GL rows hold positive amounts with a Dr/Cr flag; reversed postings are flagged Y.
        $drMinusCr = "CASE WHEN H.dr_cr_ind = 'Dr' THEN H.tran_amount ELSE -H.tran_amount END";

        // Sale lines of active orders, priced in the unit they were sold in.
        $price = $this->saleLinePriceSql();
        $cost = $this->saleLineCostSql();
        $saleFrom = "FROM order_items O INNER JOIN orders Q ON Q.id = O.order_id " . $this->saleLineJoins();
        $saleWhere = " WHERE O.status = 'Active' AND Q.status = 'Active'" . $scope('O');
        $saleLines = $saleFrom . $saleWhere;
        $todayLines = "$saleLines AND DATE(COALESCE(Q.tran_date, O.created_at)) = '$today'";

        $data = DB::select("SELECT
            (SELECT COUNT(id) FROM institutions WHERE status ='Active'" . ($isNotAdmin ? " AND id = $institutionId" : "") . ") AS total_institutions,
            (SELECT COUNT(id) FROM branches B WHERE status ='Active'" . $instScope('B') . ") AS total_branches,
            (SELECT COUNT(id) FROM users U WHERE status ='Active'" . $scope('U') . ") AS total_users,
            (SELECT COUNT(id) FROM members M WHERE status ='Active'" . $scope('M') . ") AS total_customers,
            (SELECT COALESCE(SUM(amount), 0) FROM temp_collections T WHERE status ='Pending'" . $scope('T') . ") AS total_pending_collection,
            (SELECT COALESCE(SUM(amount), 0) FROM temp_collections T WHERE status ='Declined'" . $scope('T') . ") AS total_declined_collection,
            (SELECT COALESCE(SUM(amount), 0) FROM collections C WHERE status ='Active'" . $scope('C') . ") AS total_approved_collection,
            (SELECT COUNT(id) FROM products P WHERE status ='Active'" . $instScope('P') . ") AS products,
            (SELECT COUNT(id) FROM orders Q WHERE status ='Active'" . $scope('Q') . ") AS sales,
            (SELECT COALESCE(SUM(O.qty * $price), 0) $saleLines) AS product_sold,
            (SELECT COALESCE(SUM($drMinusCr), 0) FROM gl_histories H WHERE H.reversal_flag = 'N' AND H.acct_no IN ($inventoryAccts)) AS total_stock_value,
            (SELECT COALESCE(SUM(H.tran_amount), 0) FROM gl_histories H WHERE H.reversal_flag = 'N' AND H.dr_cr_ind = 'Dr' AND H.acct_no IN ($inventoryAccts)
                AND H.tran_cd = 'STI' AND DATE(H.transaction_date) = '$today') AS today_stock_value,
            (SELECT COALESCE(-SUM($drMinusCr), 0) FROM gl_histories H WHERE H.reversal_flag = 'N' AND H.acct_no IN ($revenueAccts)) AS total_sales_value,
            (SELECT COALESCE(-SUM($drMinusCr), 0) FROM gl_histories H WHERE H.reversal_flag = 'N' AND H.acct_no IN ($revenueAccts)
                AND DATE(H.transaction_date) = '$today') AS today_sales_value,
            (SELECT COALESCE(ROUND(CAST(SUM(O.qty * ($price - $cost)) AS NUMERIC), 2), 0) $todayLines) AS today_mark_up,
            (SELECT COALESCE(ROUND(CAST(SUM(O.qty * ($price - $cost)) * 100 / NULLIF(SUM(O.qty * $cost), 0) AS NUMERIC), 2), 0) $todayLines) AS today_mark_up_percentage,
            (SELECT COALESCE(SUM($drMinusCr), 0) FROM gl_histories H INNER JOIN gl_accounts A ON H.acct_no = A.acct_no
                WHERE A.acct_type = 'EXPENSE' AND H.reversal_flag = 'N' AND H.acct_no NOT IN ($tradingCostAccts)" . $scope('H') . ") AS total_expenses,
            (SELECT COALESCE(-SUM($drMinusCr), 0) FROM gl_histories H INNER JOIN gl_accounts A ON H.acct_no = A.acct_no
                WHERE (A.acct_type = 'INCOME' OR H.acct_no IN ($tradingCostAccts)) AND H.reversal_flag = 'N'" . $scope('H') . ") AS total_income,
            (SELECT COALESCE(SUM($drMinusCr), 0) FROM gl_histories H INNER JOIN gl_accounts A ON H.acct_no = A.acct_no
                WHERE A.acct_type = 'EXPENSE' AND H.reversal_flag = 'N' AND H.acct_no NOT IN ($tradingCostAccts)" . $scope('H') . " AND DATE(H.transaction_date) = '$today') AS today_expenses,
            (SELECT COALESCE(-SUM($drMinusCr), 0) FROM gl_histories H INNER JOIN gl_accounts A ON H.acct_no = A.acct_no
                WHERE (A.acct_type = 'INCOME' OR H.acct_no IN ($tradingCostAccts)) AND H.reversal_flag = 'N'" . $scope('H') . " AND DATE(H.transaction_date) = '$today') AS today_income,
            (SELECT COUNT(id) FROM investments I WHERE status ='Active'" . $scope('I') . ") AS total_active_investments,
            (SELECT COUNT(id) FROM investments I WHERE status ='Withdrawn'" . $scope('I') . ") AS total_withdrawn_investments,
            (SELECT COALESCE(SUM(I.quantity * P.monitory), 0) FROM investments I INNER JOIN projects P ON P.id = I.project_id WHERE 1 = 1" . $scope('I') . ") AS total_investment_value,
            (SELECT COUNT(id) FROM buildings B WHERE status ='Active'" . $scope('B') . ") AS total_buildings,
            (SELECT COUNT(id) FROM building_units B WHERE status ='Active'" . $scope('B') . ") AS total_building_units_available,
            (SELECT COUNT(id) FROM building_units B WHERE status ='Occupied'" . $scope('B') . ") AS total_building_units_occupied,
            (SELECT COALESCE(SUM(rent), 0) FROM building_units B WHERE (status ='Occupied' OR status ='Active')" . $scope('B') . ") AS monthly_rent_income
            ");

        $monthlyCounts = fn ($table, $extra) => DB::select("SELECT COALESCE(COUNT(c.id), 0) AS count, date_trunc('MONTH', gs.month) AS month FROM
            generate_series((NOW() - INTERVAL '11 MONTH')::date, NOW()::date, '1 month'::interval)
            AS gs(month) LEFT JOIN $table c ON date_trunc('MONTH', c.created_at) = date_trunc('MONTH', gs.month) $extra
            GROUP BY date_trunc('MONTH', gs.month) ORDER BY date_trunc('MONTH', gs.month)");

        $collectionGraph = $monthlyCounts('collections', $scope('c'));
        $membersGraph = $monthlyCounts('members', $scope('c'));
        $productsGraph = $monthlyCounts('products', $instScope('c'));
        // reversed sales are not sales
        $salesGraph = $monthlyCounts('orders', "AND c.status = 'Active'" . $scope('c'));

        // Quantity is summed in the base unit so mixed-unit sales of a product add up;
        // the markup comes from each line's own price and cost.
        $bestSellingProduct = DB::select("SELECT P.name, M.name AS unit, MAX(S.selling_price) AS selling_price,
            SUM(COALESCE(O.base_qty, O.qty)) AS quantity,
            ROUND(CAST(SUM(O.qty * $price) AS NUMERIC), 2) AS revenue,
            ROUND(CAST(SUM(O.qty * ($price - $cost)) AS NUMERIC), 2) AS markup
            $saleFrom
            INNER JOIN products P ON P.id = O.product_id
            LEFT JOIN measurement_units M ON M.id = P.measurement_unit_id
            $saleWhere
            GROUP BY P.id, P.name, M.name
            ORDER BY quantity DESC
            LIMIT 10");
        // read straight from stock: the old query only saw products that had been sold
        $productAboutToExpire = DB::select("SELECT P.name, S.purchase_price, S.selling_price, S.quantity,
            (S.expiry_date::date - DATE '$today') AS days_difference, S.expiry_date
            FROM stocks S INNER JOIN products P ON P.id = S.product_id
            WHERE S.expiry_date IS NOT NULL AND S.expiry_date::date >= DATE '$today' AND S.quantity > 0" . $scope('S') . "
            ORDER BY days_difference ASC
            LIMIT 10");

        return $this->genericResponse(true, "Stats", 200, [
            "count" => $data,
            "collectionGraph" => $collectionGraph,
            "membersGraph" => $membersGraph,
            "productsGraph" => $productsGraph,
            "salesGraph" => $salesGraph,
            "bestSellingProduct" => $bestSellingProduct,
            "productAboutToExpire" => $productAboutToExpire
        ], "getDashboardStats", []);
    }


    public function setDailyNotification()
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();
            // Logic to set daily notifications
            // This could involve checking for pending tasks, reminders, etc.
            // For now, we will just log a message
            Log::info("Daily notification set for user: {$userData->id}");
            // return $this->genericResponse(true, "Daily notification set successfully", 200, [], "setDailyNotification", []);
        } catch (\Throwable $th) {
            Log::error("Error setting daily notification: " . $th->getMessage());
            // return $this->genericResponse(false, $th->getMessage(), 500, [], "setDailyNotification", []);
        }
    }


    public function getNotificationTypes(Request $request)
    {
        try {
            $query = NotificationType::query();
            $query->when($request->id, fn($q, $id) => $q->where('id', $id));
            $query->when($request->name, fn($q, $name) => $q->where('name', 'like', "%$name%"));
            $query->when($request->status, fn($q, $status) => $q->where('status', $status));
            $query->when($request->type, fn($q, $type) => $q->where('type', $type));
            $notificationTypes = $query->get();
            return $this->genericResponse(true, "Notification Types", 200, $notificationTypes, "getNotificationTypes", $request);
        } catch (\Throwable $th) {
            Log::error("Error notification types: " . $th->getMessage());
            return $this->genericResponse(false, $th->getMessage(), 500, [], "getNotificationTypes", $request);
        }
    }


    public function createNotificationConfig(Request $request){
        try {
            DB::beginTransaction();
            $userData = $this->getUserLogin();
                $notification = Notification::create([
                    'notification_type_id'=>$request->notification_type_id,
                    'category'=>$request->category,
                    'user_id'=>$request->user_id,
                    'member_id'=>$request->member_id,
                    'customer_id'=>$request->customer_id,
                    'supplier_id'=>$request->supplier_id,
                    'manufacturer_id'=>$request->manufacturer_id,
                    'name'=>$request->name,
                    'email'=>$request->email,
                    'phone'=>$request->phone,
                    'daily'=>$request->daily,
                    'weekly'=>$request->weekly,
                    'monthly'=>$request->monthly,
                    'quarterly'=>$request->quarterly,
                    'yearly'=>$request->yearly,
                    'description'=>$request->description,
                    'institution_id'=>$userData->institution_id,
                    'branch_id'=>$userData->branch_id,
                    'created_by'=>$userData->id,
                    'created_on'=>now(),
                ]);
            DB::commit();
            return $this->genericResponse(true, "Notification configured successfully", 201,  $notification, 'createNotificationConfig', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, [], "createNotificationConfig", $request);
        }
    }

    public function getPrinterConfigs(){
        try{
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();
            $query = DB::table('printer_configs');
            if($isNotAdmin){
                $query->where('institution_id', $userData->institution_id);
                    //   ->where('branch_id', $userData->branch_id);
            }
            $printerConfigs = $query->first();
            return $this->genericResponse(true, "Printer Configs", 200, $printerConfigs, "getPrinterConfigs", []);
        }catch(\Throwable $th){
            return $this->genericResponse(false, $th->getMessage(), 500, [], "getPrinterConfigs", []);
        }
    }

    /**
     * Get paginated EfrisOutBound records with search and filtering
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEfrisOutBound(Request $request)
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();

            // Get pagination parameters
            $perPage = $request->input('per_page', 15);
            $page = $request->input('page', 1);

            // Build query
            $query = EfrisOutBound::query();

            // Apply institution and branch filtering for non-admin users
            if ($isNotAdmin) {
                $query->where('institution_id', $userData->institution_id)
                      ->where('branch_id', $userData->branch_id);
            }

            // Search functionality
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('reference_no', 'like', "%{$search}%")
                      ->orWhere('trnsaction_type', 'like', "%{$search}%")
                      ->orWhere('status', 'like', "%{$search}%")
                      ->orWhere('action', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('uri', 'like', "%{$search}%");
                });
            }

            // Filter by status
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }

            // Filter by is_sent
            if ($request->has('is_sent') && $request->is_sent !== null && $request->is_sent !== '') {
                $query->where('is_sent', $request->is_sent);
            }

            // Filter by transaction type
            if ($request->has('trnsaction_type') && $request->trnsaction_type) {
                $query->where('trnsaction_type', 'like', "%{$request->trnsaction_type}%");
            }

            // Filter by action
            if ($request->has('action') && $request->action) {
                $query->where('action', 'like', "%{$request->action}%");
            }

            // Filter by date range
            if ($request->has('start_date') && $request->start_date) {
                $query->whereDate('created_on', '>=', $request->start_date);
            }
            if ($request->has('end_date') && $request->end_date) {
                $query->whereDate('created_on', '<=', $request->end_date);
            }

            // Order by created_on descending (most recent first)
            $query->orderBy('id', 'desc');

            // Paginate results
            $efrisOutBound = $query->paginate($perPage, ['*'], 'page', $page);

            return $this->genericResponse(true, "EfrisOutBound records retrieved successfully", 200, $efrisOutBound, "getEfrisOutBound", $request);
        } catch (\Throwable $th) {
            Log::error("Error fetching EfrisOutBound: " . $th->getMessage());
            return $this->genericResponse(false, $th->getMessage(), 500, [], "getEfrisOutBound", $request);
        }
    }


    public function consumePaddingEfrisOutBound(Request $request){
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();

            $message = "";

            $outBound = EfrisOutBound::where(['is_sent' => false, 'id' => $request->id, 'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id])->first();

            if (!$outBound) {
                return $this->genericResponse(false, "EfrisOutBound record not found or already sent", 404, [], "consumePaddingEfrisOutBound", $request);
            }

            $response = $this->efrisService->request(
                $outBound['http_method'],
                $outBound['uri'],
                [],
                $outBound['payload'],
            );

            $res = $response->json();

            if (isset($res) && isset($res['status'])){

                if($res['status']['success']){
                    $message = "EfrisOutBound record consumed successfully and sent to Efris";
                }else{
                    $message = "EfrisOutBound record consumed but failed to send to Efris: " . ($res['status']['returnMessage'] ?? 'Unknown error');
                }
                $outBound->is_sent = $res['status']['success'];
                $outBound->status = "Active";
                $outBound->return_code = $res['status']['returnCode'];
                $outBound->return_message = $res['status']['returnMessage'];
            }else{
                $message = "EfrisOutBound record consumed but received invalid response from Efris";
            }

            $outBound->response = $res;

            $outBound->save();

            return $this->genericResponse(true, $message, 200, $outBound, "consumePaddingEfrisOutBound", $request);

        }catch (\Throwable $th) {
            Log::error("Error consuming pending EfrisOutBound: " . $th->getMessage());
            return $this->genericResponse(false, $th->getMessage(), 500, [], "consumePaddingEfrisOutBound", $request);
        }
    }

}
