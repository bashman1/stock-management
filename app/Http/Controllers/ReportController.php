<?php

namespace App\Http\Controllers;

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
        return $this->genericResponse(true, "Product list", 200, $products, "getInventoryReport", $request);
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
        O.institution_id, O.created_on,S.ref_no, S.receipt_no, S.tran_id,I.name AS institution_name,
        CONCAT(U.first_name,' ',U.last_name,' ', U.other_name) AS user_name, K.purchase_price,
        K.selling_price FROM order_items O
        INNER JOIN orders S ON S.id = O.order_id
        INNER JOIN products P ON P.id = O.product_id
        INNER JOIN stocks K ON K.product_id = O.product_id
        INNER JOIN institutions I ON I.id =  O.institution_id
        LEFT JOIN users U ON U.id = O.created_by";
        // $sqlString .= " WHERE O.product_id= $request->productId";
        $sqlString .= " WHERE O.product_id= $request->productId";
        if ($isNotAdmin) {
            $sqlString .= " AND O.institution_id = $userData->institution_id AND O.branch_id = $userData->branch_id";
        }
        $sqlString .= " ORDER BY O.id DESC";
        $salesHistory = DB::select($sqlString);
        //return $this->genericResponse(true, "Product list", 200, ['institution_id'=>$userData->institution_id, 'branch_id'=>$userData->branch_id]);
        return $this->genericResponse(true, "Product list", 200, $salesHistory, "getSalesHistoryReport", $request);
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
        //TODO get products belonging to a store

        return Excel::download(new ProductCsvExport, 'users.csv');
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
        //TODO get products belonging to a store

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
        $sqlString = "SELECT O.id, O.order_id, O.product_id, O.qty AS quantity, O.status, O.institution_id, O.branch_id,
            O.created_by, O.updated_by, O.updated_on, O.created_at, O.updated_at, P.name AS product_name,
            P.description, P.product_no, P.ref_no, Q.ref_no AS transaction_ref, Q.receipt_no, Q.tran_id,
            COALESCE(R.selling_price, S.selling_price) AS selling_price, S.purchase_price, M.name as measurement_unit FROM order_items O
            INNER JOIN products P ON O.product_id = P.id
            INNER JOIN orders Q ON Q.id = O.order_id
            INNER JOIN stocks S ON S.branch_id = O.branch_id AND S.product_id = O.product_id
            INNER JOIN measurement_units  M ON M.id = P.measurement_unit_id
            LEFT JOIN order_items R ON Q.id = R.order_id";

        // $sqlString .= " WHERE O.status = '$request->status' ";
        // if ($isNotAdmin) {
        //     $sqlString .= " AND O.institution_id = $userData->institution_id AND O.branch_id = $userData->branch_id";
        // }
        $conditions = [];
        if (isset($request->status)) {
            $conditions[] = "O.status = '" . addslashes($request->status) . "'";
        }
        if ($isNotAdmin) {
            $conditions[] = "O.institution_id = " . intval($userData->institution_id);
            $conditions[] = "O.branch_id = " . intval($userData->branch_id);
        }
        if (isset($request->from_date) && isset($request->to_date)) {
            $from_date = date('Y-m-d', strtotime($request->from_date));
            $to_date   = date('Y-m-d', strtotime($request->to_date));
            $conditions[] = "DATE(Q.tran_date) BETWEEN '$from_date' AND '$to_date'";
        } else if (isset($request->from_date)) {
            $conditions[] = "DATE(Q.tran_date) >= '" . addslashes($request->from_date) . "'";
        } else if (isset($request->to_date)) {
            $conditions[] = "DATE(Q.tran_date) <= '" . addslashes($request->to_date) . "'";
        }
        if (isset($request->searchKey)) {
            $searchKey = addslashes($request->searchKey);
            $conditions[] = "(P.name ILIKE '%$searchKey%' OR P.product_no ILIKE '%$searchKey%'
            OR Q.ref_no ILIKE '%$searchKey%' OR Q.receipt_no ILIKE '%$searchKey%'
            OR Q.tran_id ILIKE '%$searchKey%')";
        }
        if (count($conditions) > 0) {
            $sqlString .= " WHERE " . implode(" AND ", $conditions);
        }

        $sqlString .= " ORDER BY O.id DESC";
        return DB::select($sqlString);
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
                ['header' => 'Date', 'key' => 'created_at'],
                ['header' => 'Name', 'key' => 'product_name'],
                ['header' => 'Quantity Sold', 'key' => 'quantity'],
                ['header' => 'Measurement Unit', 'key' => 'measurement_unit'],
                ['header' => 'Amount', 'key' => 'selling_price'],
                ['header' => 'Selling Price', 'key' => 'selling_price'],
                ['header' => 'Profit Margin', 'key' => 'purchase_price'],
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



            $totalSql ="SELECT COUNT(R.id) AS total
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
