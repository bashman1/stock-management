<?php

namespace App\Http\Controllers;

use App\Exports\GenericCSVExport;
use App\Exports\SalesItemReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ProductCsvExport;
use App\Models\Institution;
use App\Models\InstitutionContact;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\ReportsService;
// use Illuminate\Container\Attributes\Log;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{

    protected $reportService;
    public function __construct(ReportsService $reportServ)
    {
        $this->reportService = $reportServ;
    }


    public function getInventoryReport(Request $request)
    {
        $products = $this->getInventoryReportData($request);
        return $this->genericResponse(true, "Product list", 200, $products, "getInventoryReport", $request);
    }



    public function getInventoryReportData($request)
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();
            $sqlString =  "SELECT P.id, P.name, P.product_no, P.category_id, P.sub_category_id, P.manufacturer_id,
                P.supplier_id, P.measurement_unit_id, P.description, P.institution_id, P.user_id,P.status,
                P.created_by, P.updated_by, P.created_on, P.updated_on, P.created_at, P.updated_at,
                P.type_id, P.gauge_id, C.name AS category_name, S.name AS sub_category_name,
                M.name AS manufacturer_name, M.website, M.email,M.phone_number,M.country_id,
                M.description AS manufacturer_description, R.name AS manufacture_country,
                K.name AS measurement_unit_name,I.name AS institution_name, P.tax_config,
                T.name AS supplier_name, CONCAT(U.first_name, ' ', U.last_name,' ', U.other_name) AS user_name,
                Q.name AS type_name, G.name AS gauge_name, Y.purchase_price, Y.selling_price,
                Y.quantity, Y.min_quantity, Y.max_quantity, Y.stock_date,Y.manufactured_date,
                Y.expiry_date, B.name AS branch_name FROM products P
                LEFT JOIN product_categories C ON C.id = P.category_id
                LEFT JOIN product_sub_categories S ON S.id=P.sub_category_id
                LEFT JOIN manufacturers M ON M.id =P.measurement_unit_id
                LEFT JOIN country_refs R ON M.country_id =R.id
                LEFT JOIN suppliers T ON T.id = P.supplier_id
                INNER JOIN measurement_units K ON K.id = P.measurement_unit_id
                INNER JOIN institutions I ON I.id = P.institution_id
                INNER JOIN users U ON U.id = P.user_id
                LEFT JOIN product_types Q ON Q.id = P.type_id
                LEFT JOIN product_gauges G ON G.id = P.gauge_id
                INNER JOIN stocks Y ON P.id = Y.product_id
                INNER JOIN branches B ON Y.branch_id = B.id";
            $sqlString .= " WHERE P.status = '$request->status'";
            if ($isNotAdmin) {
                $sqlString .= " AND P.institution_id = $userData->institution_id AND Y.branch_id = $userData->branch_id";
            }
            $sqlString .= " ORDER BY P.id DESC";
            $products = DB::select($sqlString);
            return $products;
        } catch (\Throwable $th) {
            throw $th;
        }
    }



    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function getSalesReport(Request $request)
    {

        $orders = $this->reportService->salesReport($request);

        return $this->genericResponse(true, "Product list", 200, $orders, "getSalesReport", $request);
    }


    public function getInventoryHistoryReport(Request $request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();
        $sqlString = "SELECT P.name AS product_name, H.id, H.purchase_price, H.selling_price, H.product_id, H.stock_id, H.quantity, H.min_quantity,
        H.max_quantity, H.institution_id, COALESCE(H.stock_date, H.created_on) AS stock_date, H.manufactured_date,
        H.expiry_date, I.name AS institution_name, CONCAT(U.first_name,' ',U.last_name,' ', U.other_name) AS user_name
        FROM stock_histories H
        INNER JOIN products P ON P.id = H.product_id
        INNER JOIN institutions I ON I.id =  H.institution_id
        INNER JOIN users U ON U.id = H.user_id";
        $sqlString .= " WHERE H.product_id= $request->productId";
        if ($isNotAdmin) {
            $sqlString .= " AND H.institution_id = $userData->institution_id AND H.branch_id = $userData->branch_id";
        }
        $sqlString .= " ORDER BY H.id DESC";
        $stockHistory = DB::select($sqlString);
        return $this->genericResponse(true, "Product list", 200, $stockHistory, "getInventoryHistoryReport", $request);
    }

    public function getSalesHistoryReport(Request $request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();
        $sqlString = "SELECT P.name AS product_name, O.id, O.order_id, O.product_id, O.qty AS quantity, O.status,
        O.institution_id, O.created_on, COALESCE(S.tran_date, O.created_on) AS tran_date,
        S.ref_no, S.receipt_no, S.tran_id,I.name AS institution_name,
        CONCAT(U.first_name,' ',U.last_name,' ', U.other_name) AS user_name,
        COALESCE(O.purchase_price, K.purchase_price) AS purchase_price,
        COALESCE(O.selling_price, K.selling_price) AS selling_price,
        COALESCE(X.name, M.name) AS measurement_unit FROM order_items O
        INNER JOIN orders S ON S.id = O.order_id
        INNER JOIN products P ON P.id = O.product_id
        -- stocks holds one row per product PER BRANCH; joining on product_id alone
        -- duplicated every sale line for a product stocked in several branches
        LEFT JOIN stocks K ON K.product_id = O.product_id AND K.branch_id = O.branch_id
        INNER JOIN measurement_units M ON M.id = P.measurement_unit_id
        LEFT JOIN measurement_units X ON X.id = O.measurement_unit_id
        INNER JOIN institutions I ON I.id =  O.institution_id
        LEFT JOIN users U ON U.id = O.created_by";
        $sqlString .= " WHERE O.product_id= $request->productId";
        // reversed sales restore stock, so they are not sales history
        $sqlString .= " AND COALESCE(O.status,'') <> 'Deleted' AND COALESCE(S.status,'') <> 'Deleted'";
        if ($isNotAdmin) {
            $sqlString .= " AND O.institution_id = $userData->institution_id AND O.branch_id = $userData->branch_id";
        }
        $sqlString .= " ORDER BY O.id DESC";
        $salesHistory = DB::select($sqlString);
        //return $this->genericResponse(true, "Product list", 200, ['institution_id'=>$userData->institution_id, 'branch_id'=>$userData->branch_id]);
        return $this->genericResponse(true, "Product list", 200, $salesHistory, "getSalesHistoryReport", $request);
    }

    /**
     * Stock statement: every restock (in) and sale (out) for every product on one
     * timeline, with a per-product running balance — a bank statement for stock.
     *
     * Quantities are in the product's BASE unit so both sides are comparable:
     * stock_histories.quantity is already base, while order_items.qty is in the
     * unit the cashier sold in (base_qty is its base equivalent). Money is the
     * other way round — order_items.selling_price is per CHOSEN unit, so value is
     * qty * selling_price, not base_qty * selling_price.
     *
     * The running balance is computed over each product's whole history and the
     * date range is applied afterwards, so a filtered statement still opens at the
     * correct balance instead of restarting at zero.
     */
    public function getStockStatement(Request $request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();

        $scope = "";
        if ($isNotAdmin) {
            $scope = " AND %s.institution_id = " . intval($userData->institution_id)
                   . " AND %s.branch_id = " . intval($userData->branch_id);
        }

        // --- movement filters applied BEFORE the running balance ---
        // User input is bound, not interpolated: addslashes (used elsewhere in this
        // file) is not a valid escape for PostgreSQL, which treats a backslash in a
        // literal as an ordinary character under standard_conforming_strings.
        $bindings = [];
        $movementWhere = [];
        if (!empty($request->productId)) {
            $movementWhere[] = "product_id = " . intval($request->productId);
        }
        if (!empty(trim((string) $request->searchKey))) {
            $like = '%' . trim($request->searchKey) . '%';
            $movementWhere[] = "(product_name ILIKE ? OR product_no ILIKE ? OR COALESCE(reference,'') ILIKE ?)";
            array_push($bindings, $like, $like, $like);
        }
        $movementSql = count($movementWhere) ? " WHERE " . implode(" AND ", $movementWhere) : "";

        $restocks = "SELECT 'RESTOCK' AS movement_type, H.id AS source_id,
            COALESCE(H.stock_date, H.created_on) AS tran_date,
            H.product_id, P.name AS product_name, P.product_no,
            NULL AS reference,
            H.quantity AS in_qty, 0 AS out_qty,
            COALESCE(H.purchase_price, 0) AS unit_price,
            (H.quantity * COALESCE(H.purchase_price, 0)) AS value,
            M.name AS unit,
            CONCAT(U.first_name,' ',U.last_name) AS user_name
            FROM stock_histories H
            INNER JOIN products P ON P.id = H.product_id
            LEFT JOIN measurement_units M ON M.id = P.measurement_unit_id
            LEFT JOIN users U ON U.id = H.user_id
            WHERE COALESCE(H.status,'') <> 'Deleted'" . sprintf($scope, 'H', 'H');

        $sales = "SELECT 'SALE' AS movement_type, O.id AS source_id,
            COALESCE(S.tran_date, O.created_on) AS tran_date,
            O.product_id, P.name AS product_name, P.product_no,
            S.receipt_no AS reference,
            0 AS in_qty, COALESCE(O.base_qty, O.qty) AS out_qty,
            COALESCE(O.selling_price, K.selling_price, 0) AS unit_price,
            (O.qty * COALESCE(O.selling_price, K.selling_price, 0)) AS value,
            M.name AS unit,
            CONCAT(U.first_name,' ',U.last_name) AS user_name
            FROM order_items O
            INNER JOIN orders S ON S.id = O.order_id
            INNER JOIN products P ON P.id = O.product_id
            -- stocks holds one row per product PER BRANCH; without the branch
            -- predicate a multi-branch product duplicates every sale line
            LEFT JOIN stocks K ON K.product_id = O.product_id AND K.branch_id = O.branch_id
            LEFT JOIN measurement_units M ON M.id = P.measurement_unit_id
            LEFT JOIN users U ON U.id = O.created_by
            WHERE COALESCE(O.status,'') <> 'Deleted' AND COALESCE(S.status,'') <> 'Deleted'" . sprintf($scope, 'O', 'O');

        // date range applied outside the window so the balance carries forward
        $dateWhere = [];
        if (!empty($request->from_date)) {
            $dateWhere[] = "tran_date::date >= ?";
            $bindings[] = date('Y-m-d', strtotime($request->from_date));
        }
        if (!empty($request->to_date)) {
            $dateWhere[] = "tran_date::date <= ?";
            $bindings[] = date('Y-m-d', strtotime($request->to_date));
        }
        if (!empty($request->movementType) && in_array($request->movementType, ['RESTOCK', 'SALE'])) {
            $dateWhere[] = "movement_type = ?";
            $bindings[] = $request->movementType;
        }
        $outerSql = count($dateWhere) ? " WHERE " . implode(" AND ", $dateWhere) : "";

        // Current stock on hand, carried on every row so the UI can show the
        // statement balance next to it. They can legitimately differ: restock()
        // appends a true received-quantity row, but editing a product overwrites
        // the latest stock_histories row with an absolute quantity instead of
        // appending a movement, so quantity changed that way is not in the ledger.
        $onHandScope = $isNotAdmin ? " WHERE branch_id = " . intval($userData->branch_id) : "";
        $onHand = "SELECT product_id, SUM(quantity) AS quantity FROM stocks $onHandScope GROUP BY product_id";

        $sqlString = "SELECT t.*, COALESCE(OH.quantity, 0) AS stock_on_hand FROM (
            SELECT mv.*,
                SUM(mv.in_qty - mv.out_qty) OVER (
                    PARTITION BY mv.product_id
                    ORDER BY mv.tran_date, mv.movement_type, mv.source_id
                    ROWS UNBOUNDED PRECEDING
                ) AS balance
            FROM ( $restocks UNION ALL $sales ) mv
            $movementSql
        ) t
        LEFT JOIN ( $onHand ) OH ON OH.product_id = t.product_id
        $outerSql ORDER BY product_name ASC, tran_date ASC, movement_type ASC, source_id ASC";

        $statement = DB::select($sqlString, $bindings);
        return $this->genericResponse(true, "Stock statement", 200, $statement, "getStockStatement", $request);
    }

    public function downLoadProductPdfReport()
    {
        //TODO get products belonging to a store

        $products = Product::with(['stock', 'category', 'subCategory', 'productType', 'measurement'])->get();

        //return response()->json(['result'=>$products]);
        if (count($products) > 0) {
            Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
            $pdf = Pdf::loadView('product.reports', compact('products'));
            $pdf->setPaper('A4', 'landscape');
            $pdf->setBasePath(public_path());
            $pdf->setPaper('A4', 'landscape');
            $pdf->setBasePath(public_path());
            return $pdf->stream('product_report' . '.pdf');
        }
    }


    public function downLoadProductCsvReport()
    {
        $parameter= (object) ["status" => "Active"];
        $products = $this->getInventoryReportData($parameter);

        $genericData = [
            'data' => $products,
            'headers' => [
                ['header' => 'Product Name', 'key' => 'name'],
                ['header' => 'Product No', 'key' => 'product_no'],
                ['header' => 'Category', 'key' => 'category_name'],
                ['header' => 'Sub Category', 'key' => 'sub_category_name'],
                ['header' => 'Manufacturer', 'key' => 'manufacturer_name'],
                ['header' => 'Supplier', 'key' => 'supplier_name'],
                ['header' => 'Measurement Unit', 'key' => 'measurement_unit_name'],
                ['header' => 'Purchase Price', 'key' => 'purchase_price'],
                ['header' => 'Selling Price', 'key' => 'selling_price'],
                ['header' => 'Quantity in Stock', 'key' => 'quantity'],
                ['header' => 'Min Quantity', 'key' => 'min_quantity'],
                ['header' => 'Max Quantity', 'key' => 'max_quantity'],
                ['header' => 'Stock Date', 'key' => 'stock_date'],
                ['header' => 'Manufactured Date', 'key' => 'manufactured_date'],
                ['header' => 'Expiry Date', 'key' => 'expiry_date'],
            ]
        ];
        return Excel::download(new GenericCSVExport($genericData), 'product_report.csv');
    }


    public function downLoadSalesPdfReport(Request $request)
    {


        $sales = $this->reportService->salesReport($request);

        //return response()->json(['result'=>$products]);
        if (count($sales) > 0) {
            Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
            $pdf = Pdf::loadView('sales.reports', compact('saless'));
            $pdf->setPaper('A4', 'patriot');
            $pdf->setBasePath(public_path());
            $pdf->setPaper('A4', 'patriot');
            $pdf->setBasePath(public_path());
            return $pdf->stream('sales_report' . '.pdf');
        }
    }

    public function downLoadSalesCsvReport()
    {

        return Excel::download(new ProductCsvExport, 'users.csv');
    }

    public function getItemSalesReport(Request $request)
    {
        $salesReport = $this->getItemSalesData($request);
        return $this->genericResponse(true, "Sales list", 200, $salesReport, "getItemSalesReport", $request);
    }

    public function getItemSalesData($request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();
        $price = $this->saleLinePriceSql();
        $cost = $this->saleLineCostSql();
        // qty, selling_price and purchase_price are all in the unit the line was sold
        // in, so amount and profit are qty x per-unit figures (not base_qty).
        $sqlString = "SELECT O.id, O.order_id, O.product_id, O.qty AS quantity, O.status, O.institution_id, O.branch_id,
            O.created_by, O.updated_by, O.updated_on, O.created_at, O.updated_at,
            COALESCE(Q.tran_date, O.created_at) AS tran_date, P.name AS product_name,
            P.description, P.product_no, P.ref_no, Q.ref_no AS transaction_ref, Q.receipt_no, Q.tran_id,
            ROUND(CAST($price AS NUMERIC), 2) AS selling_price,
            ROUND(CAST($cost AS NUMERIC), 2) AS purchase_price,
            ROUND(CAST(O.qty * $price AS NUMERIC), 2) AS amount,
            ROUND(CAST(O.qty * ($price - $cost) AS NUMERIC), 2) AS profit,
            COALESCE(X.name, M.name) AS measurement_unit FROM order_items O
            INNER JOIN products P ON O.product_id = P.id
            INNER JOIN orders Q ON Q.id = O.order_id
            INNER JOIN measurement_units  M ON M.id = P.measurement_unit_id
            LEFT JOIN measurement_units X ON X.id = O.measurement_unit_id" . $this->saleLineJoins();

        // User input is bound, not interpolated: addslashes is not a valid escape for PostgreSQL.
        $bindings = [];
        // a reversed sale marks both the order and its lines Deleted
        $conditions = ["COALESCE(Q.status, '') <> 'Deleted'"];
        if (isset($request->status)) {
            $conditions[] = "O.status = ?";
            $bindings[] = $request->status;
        }
        if ($isNotAdmin) {
            $conditions[] = "O.institution_id = " . intval($userData->institution_id);
            $conditions[] = "O.branch_id = " . intval($userData->branch_id);
        }
        $fromDate = $this->safeDate($request->from_date);
        $toDate = $this->safeDate($request->to_date);
        if ($fromDate) {
            $conditions[] = "DATE(COALESCE(Q.tran_date, O.created_at)) >= ?";
            $bindings[] = $fromDate;
        }
        if ($toDate) {
            $conditions[] = "DATE(COALESCE(Q.tran_date, O.created_at)) <= ?";
            $bindings[] = $toDate;
        }
        if (!empty(trim((string) $request->searchKey))) {
            $like = '%' . trim($request->searchKey) . '%';
            $conditions[] = "(P.name ILIKE ? OR P.product_no ILIKE ? OR Q.ref_no ILIKE ? OR Q.receipt_no ILIKE ? OR Q.tran_id ILIKE ?)";
            array_push($bindings, $like, $like, $like, $like, $like);
        }
        $sqlString .= " WHERE " . implode(" AND ", $conditions);

        $sqlString .= " ORDER BY O.id DESC";
        return DB::select($sqlString, $bindings);
    }

    public function downloadSalesItemCSVReport(Request $request)
    {
        // $request->status = 'Active';
        $request->merge(['status' => 'Active']);
        $salesReport = $this->getItemSalesData($request);
        return Excel::download(new SalesItemReport($salesReport), "sales_report.csv");
    }

    public function downloadSalesItemPDFReport(Request $request)
    {
        // $request->status = 'Active';
        $request->merge(['status' => 'Active']);
        $sales = $this->getItemSalesData($request);

        $genericData = [
            'data' => $sales,
            'headers' => [
                ['header' => 'Date', 'key' => 'tran_date'],
                ['header' => 'Name', 'key' => 'product_name'],
                ['header' => 'Quantity Sold', 'key' => 'quantity'],
                ['header' => 'Measurement Unit', 'key' => 'measurement_unit'],
                ['header' => 'Amount', 'key' => 'amount'],
                ['header' => 'Selling Price', 'key' => 'selling_price'],
                ['header' => 'Profit Margin', 'key' => 'profit'],
            ]
        ];

        return $this->downloadPDF('Sales Report',  'landscape', $genericData);
    }

    public function rentArrearsReport(Request $request)
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();


            $sqlString = "SELECT R.id, R.building_id, R.unit_id, R.member_id, R.duration,
                        R.amount_paid, R.status, R.ref_no, R.rental_date, R.due_date,
                        R.paid_date, R.description, R.institution_id, R.branch_id,
                        R.created_by, R.created_on, R.created_at, R.updated_at, R.payment_status,
                        B.name AS building_name, B.ref_no AS building_ref,
                        X.name AS unit_name, X.unit_type, X.unit_no,
                        X.ref_no AS unit_ref, X.rent AS unit_monthly_rent,
                        CONCAT(M.first_name,' ',M.last_name,' ',M.other_name) AS member_name,
                        M.member_number, M.alt_member_number, M.phone_number, M.email, M.gender,
                        SUM(RH.amount_paid) AS amount_paid_as_at
                        FROM rentals R
                        INNER JOIN buildings B ON B.id = R.building_id
                        INNER JOIN building_units X ON X.id = R.unit_id
                        INNER JOIN members M ON R.member_id = M.id
                        LEFT JOIN rental_histories RH ON RH.rental_id = R.id
                        ";



            $totalSql = "SELECT COUNT(R.id) AS total
                        FROM rentals R
                        INNER JOIN buildings B ON B.id = R.building_id
                        INNER JOIN building_units X ON X.id = R.unit_id
                        INNER JOIN members M ON R.member_id = M.id
                        LEFT JOIN rental_histories RH ON RH.rental_id = R.id";

            $conditions = [];
            if ($isNotAdmin) {
                $conditions[] = "R.institution_id = " . intval($userData->institution_id);
                // $conditions[] = "R.branch_id = " . intval($userData->branch_id);
            }
            if (isset($request->searchKey)) {
                $searchKey = addslashes($request->searchKey);
                $conditions[] = "(B.name ILIKE '%$searchKey%' OR B.ref_no ILIKE '%$searchKey%'
                OR X.unit_no ILIKE '%$searchKey%' OR X.unit_type ILIKE '%$searchKey%'
                OR M.first_name ILIKE '%$searchKey%' OR M.last_name ILIKE '%$searchKey%'
                OR M.other_name ILIKE '%$searchKey%' OR M.member_number ILIKE '%$searchKey%'
                OR M.alt_member_number ILIKE '%$searchKey%' OR M.phone_number ILIKE '%$searchKey%'
                OR M.email ILIKE '%$searchKey%')";
            }
            if (isset($request->from_date) && isset($request->to_date)) {
                $from_date = date('Y-m-d', strtotime($request->from_date));
                $to_date   = date('Y-m-d', strtotime($request->to_date));
                $conditions[] = "DATE(R.due_date) BETWEEN '$from_date' AND '$to_date'";
            } else if (isset($request->from_date)) {
                $conditions[] = "DATE(R.due_date) >= '" . addslashes($request->from_date) . "'";
            } else if (isset($request->to_date)) {
                $conditions[] = "DATE(R.due_date) <= '" . addslashes($request->to_date) . "'";
            }

            $conditions[] = " ( X.rent * R.duration) - COALESCE(RH.amount_paid, 0) > 0 ";

            // Log::info("Hello there ****************/********************");
            Log::info($request->from_date);

            if (count($conditions) > 0) {
                $sqlString .= " WHERE " . implode(" AND ", $conditions);
                $totalSql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sqlString .= " GROUP BY R.id, B.name, B.ref_no, X.name, X.unit_type,
                        x.unit_no, x.ref_no, x.rent, m.first_name, m.last_name,
                        m.other_name, m.member_number, m.alt_member_number,
                        m.phone_number,m.email, m.gender ";
            $sqlString .= " ORDER BY R.id DESC";
            $rentals = $this->paginate($request->rowPerPage, $totalSql, $sqlString, $request->page);
            return $this->genericResponse(true, "Rentals list", 200, $rentals, "rentArrearsReport", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, null, "rentArrearsReport", $request);
        }
    }
}
