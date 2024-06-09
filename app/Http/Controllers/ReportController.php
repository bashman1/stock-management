<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ProductCsvExport;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\ReportsService;
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

        $userData = auth()->user();
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
        return $this->genericResponse(true, "Product list", 200, $products);
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

        return $this->genericResponse(true, "Product list", 200, $orders);
    }


    public function getInventoryHistoryReport(Request $request)
    {
        $userData = auth()->user();
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
        return $this->genericResponse(true, "Product list", 200, $stockHistory);
    }

    public function getSalesHistoryReport(Request $request)
    {
        $userData = auth()->user();
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
        return $this->genericResponse(true, "Product list", 200, $salesHistory);
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
}
