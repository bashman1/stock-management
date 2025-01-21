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
        return $this->genericResponse(true, "Product list", 200, $stockHistory, "getInventoryHistoryReport", $request);
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

    public function getItemSalesReport(Request $request){
        $salesReport = $this->getItemSalesData($request);
        return $this->genericResponse(true, "Sales list", 200, $salesReport, "getItemSalesReport", $request);
    }

    public function getItemSalesData($request){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();
        $sqlString = "SELECT O.id, O.order_id, O.product_id, O.qty AS quantity, O.status, O.institution_id, O.branch_id,
            O.created_by, O.updated_by, O.updated_on, O.created_at, O.updated_at, P.name AS product_name,
            P.description, P.product_no, P.ref_no, Q.ref_no AS transaction_ref, Q.receipt_no, Q.tran_id,
            S.selling_price, S.purchase_price, M.name as measurement_unit FROM order_items O
            INNER JOIN products P ON O.product_id = P.id
            INNER JOIN orders Q ON Q.id = O.order_id
            INNER JOIN stocks S ON S.branch_id = O.branch_id AND S.product_id = O.product_id
            INNER JOIN measurement_units  M ON M.id = P.measurement_unit_id ";
        $sqlString .= " WHERE O.status = '$request->status' ";
        if ($isNotAdmin) {
            $sqlString .= " AND O.institution_id = $userData->institution_id AND O.branch_id = $userData->branch_id";
        }
        $sqlString .= " ORDER BY O.id DESC";
        return DB::select($sqlString);
    }

    public function downloadSalesItemCSVReport(Request $request){
        $request->status = 'Active';
        $salesReport = $this->getItemSalesData($request);
        return Excel::download(new SalesItemReport($salesReport), "sales.csv");
    }

    // public function downloadSalesItemPDFReport(Request $request){
    //     $request->status = 'Active';
    //     $sales = $this->getItemSalesData($request);
    //     if (count($sales) > 0) {
    //         Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);

    //         $pdf = Pdf::loadView('sales.salesItem', compact('sales'));
    //         // $pdf->getDomPDF()->set_option("enable_php", true);
    //         $pdf->setBasePath(public_path());
    //         $pdf->setPaper('A4', 'landscape');
    //         $pdf->setBasePath(public_path());

    //         //adding page number
    //         $pdf->render();
    //         $dompdf = $pdf->getDomPDF();

    //         // Add header (custom text at the top of each page)
    //         $canvas = $dompdf->getCanvas();
    //         $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');
    //         $canvas->page_text(270, 10, "Sales Report Header", $font, 12, [0, 0, 0]);

    //         // Add page numbers to the footer (bottom of each page)
    //         // $canvas->page_text(520, 820, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 10, [0, 0, 0]);  //portrait
    //         $canvas->page_text(700, 550, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 10, [0, 0, 0]);     //landscape
    //         // end of adding page number

    //         return $pdf->stream('sales_report' . '.pdf');
    //     }
    // }


    public function downloadSalesItemPDFReport(Request $request)
{
    $request->status = 'Active';
    $sales = $this->getItemSalesData($request);

    $userData = auth()->user();
    $institution = Institution::find($userData->institution_id);
    $institutionContact = InstitutionContact::where("institution_id", $userData->institution_id)->first();

    if (count($sales) > 0) {
        // Set options and load the view
        Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
        $pdf = Pdf::loadView('sales.salesItem', compact('sales'));

        // Set paper size and orientation
        $pdf->setBasePath(public_path());
        $pdf->setPaper('A4', 'landscape');

        // Render the PDF to access the DomPDF canvas
        $pdf->render();
        $dompdf = $pdf->getDomPDF();
        $canvas = $dompdf->getCanvas();
        $font = $dompdf->getFontMetrics()->getFont('Helvetica', 'normal');

        // Add header to each page
        // $canvas->page_text(30, 30, "Document NameContact Information Address", $font, 10, [0, 0, 0]);
        $canvas->page_text(350, 15, "Sales Report", $font, 12, [0, 0, 0]);

        $canvas->page_text(30, 10, $institution->name, $font, 10, [0, 0, 0]);
        $canvas->page_text(30, 20, "Phone number:".$institutionContact->phone_number." Email:".$institutionContact->email, $font, 10, [0, 0, 0]);
        $canvas->page_text(30, 30, $institution->address, $font, 10, [0, 0, 0]);


        // Add footer to each page
        // Position and add printed by and powered by text
        $canvas->page_text(30, 550, "Powered by:Smart Collect. Printed by:".$userData->first_name.". ".$userData->last_name." ".$userData->other_name." On:".date('D d M Y'), $font, 10, [0, 0, 0]);

        // Add an image to the footer (left side)
        // $imagePath = public_path('images/SmarCollectlogo-removebg-preview.png');
        // $image = $canvas->image($imagePath, 30, 800, 50, 20); // Adjust coordinates and size as needed

        // Add page numbers to the footer (right side)
        // $canvas->page_text(700, 820, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 10, [0, 0, 0]);
        $canvas->page_text(700, 550, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 10, [0, 0, 0]);     //landscape

        // Stream the generated PDF
        return $pdf->stream('sales_report.pdf');
    }
}



}
