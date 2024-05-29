<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\stock;
use App\Models\CntrlParameter;
use App\Models\stockHistory;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\GlAccounts;
use App\Models\Institution;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ProductCsvExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{

    public function createProduct(Request $request)
    {
        $userData = auth()->user();
        $product = null;

        $institution = Institution::find($userData->institution_id);

        $isEdit = $request->id;
        $refNo = null;
        $message = null;

        if (isset($isEdit)) {
            $product = Product::find($isEdit);
            $product->updated_by = auth()->user()->id;
            $product->updated_on = Carbon::now();
            $refNo = $product->ref_no;
            $message = "Product updated successfully";
            // return $this->genericResponse(false,"testing editing", 400, $product);
        } else {
            $product = new Product();
            $product->created_by = $userData->id;
            $product->created_on = Carbon::now();
            $refNo = $this->generateRefNumber('product_ref');
            $message = "Product Created successfully";
        }

        DB::beginTransaction();
        $product->name = $request->name;
        $product->product_no = $request->product_no;
        $product->category_id = $request->category_id;
        $product->sub_category_id = $request->sub_category_id;
        $product->manufacturer_id = $request->manufacturer_id;
        $product->supplier_id = $request->supplier_id;
        $product->measurement_unit_id = $request->measurement_unit_id;
        $product->description = $request->description;
        $product->institution_id = $userData->institution_id;
        $product->user_id = $userData->id;
        $product->status = $userData->status;
        $product->type_id = $request->type_id;
        $product->gauge_id = $request->gauge_id;
        $product->ref_no = $refNo;
        if (isset($institution->is_tax_enabled) && $institution->is_tax_enabled) {
            $product->tax_config = $request->tax_config;
        }
        $product->save();

        if (!isset($isEdit)) {
            $branch = Branch::where(['id' => $userData->branch_id, 'institution_id' => $userData->institution_id])->first();

            $cl = CntrlParameter::where(['param_cd' => 'CL', 'institution_id' => $userData->institution_id])->first();
            $sti = CntrlParameter::where(['param_cd' => 'STI', 'institution_id' => $userData->institution_id])->first();

            $pia = CntrlParameter::where(['param_cd' => 'PIA', 'institution_id' => $userData->institution_id])->first();
            $pil = CntrlParameter::where(['param_cd' => 'PIL', 'institution_id' => $userData->institution_id])->first();
            $pp = CntrlParameter::where(['param_cd' => 'PP', 'institution_id' => $userData->institution_id])->first();


            $cgl = str_replace('***', $branch->code, $cl->param_value);
            $cash = GlAccounts::where('acct_no', $cgl)->first();

            $sgl = str_replace('***', $branch->code, $sti->param_value);
            $stock = GlAccounts::where('acct_no', $sgl)->first();

            $piagl = str_replace('***', $branch->code, $pia->param_value);
            $pAss = GlAccounts::where('acct_no', $piagl)->first();

            $pilgl = str_replace('***', $branch->code, $pil->param_value);
            $pLia = GlAccounts::where('acct_no', $pilgl)->first();

            $ppgl = str_replace('***', $branch->code, $pp->param_value);
            $ppE = GlAccounts::where('acct_no', $ppgl)->first();

            $tran = (object) [
                "acct_no" => $sgl,
                "acct_type" => $stock->acct_type,
                "contra_acct_no" => $cgl,
                "contra_acct_type" => $cash->acct_type,
                "description" => "Create product:- $product->name on " . date('Y-m-d H:i:s'),
                "dr_cr_ind" => "DR/CR",
                "tran_amount" => $request->quantity * $request->purchase_price,
                "reversal_flag" => 'N',
                "tran_date" => isset($request->date) ? $request->date : now(),
                "tran_cd" => 'STI',
                "tran_id" => $this->generateUuid(),
                "status" => 'Active',
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "created_on" => now(),
            ];

            $postTran = $this->postTransaction($tran);

            $debitRequest = (object) [
                "acct_no" => $sgl,
                "acct_type" => $stock->acct_type,
                "tran_amt" => $postTran->tran_amount,
                "reversal_flag" => 'N',
                "description" => $postTran->description,
                "transaction_date" => $postTran->tran_date,
                "contra_acct_no" => $cgl,
                "contra_acct_type" => $cash->acct_type,
                "tran_type" => 'STOCK IN',
                "tran_cd" => 'STI',
                "tran_id" => $postTran->tran_id,
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "status" => 'Active',
            ];

            $creditRequest = (object) [
                "acct_no" => $cgl,
                "acct_type" => $cash->acct_type,
                "tran_amt" => $postTran->tran_amount,
                "reversal_flag" => 'N',
                "description" => $postTran->description,
                "transaction_date" => $postTran->tran_date,
                "contra_acct_no" => $sgl,
                "contra_acct_type" => $stock->acct_type,
                "tran_type" => 'STOCK IN',
                "tran_cd" => 'STI',
                "tran_id" => $postTran->tran_id,
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "status" => 'Active',
            ];
            $debitStock = $this->postGlDR($debitRequest);
            $creditCash = $this->postGlCR($creditRequest);

            $debitPurchases = $debitRequest;
            $creditPurchases = $creditRequest;

            $debitPurchases->acct_no = $piagl;
            $debitPurchases->acct_type = $pAss->acct_type;
            $debitPurchases->contra_acct_no = $pilgl;
            $debitPurchases->contra_acct_type = $pLia->acct_type;

            $creditPurchases->acct_no = $pilgl;
            $creditPurchases->acct_type = $pLia->acct_type;
            $creditPurchases->contra_acct_no = $piagl;
            $creditPurchases->contra_acct_type = $pAss->acct_type;

            $onDebitPurchases = $this->postGlDR($debitPurchases);
            $onCreditPurchases = $this->postGlCR($creditPurchases);

            $passageDebitRequest = $debitRequest;
            $passageDebitRequest->acct_no = $ppgl;
            $passageDebitRequest->tran_amt = 0;
            $passageDebitRequest->acct_type = $ppE->acct_type;

            $cashCreditRequest = $creditRequest;
            $cashCreditRequest->contra_acct_no = $ppgl;
            $cashCreditRequest->tran_amt = 0;
            $cashCreditRequest->contra_acct_type = $ppE->acct_type;

            $onDebitPassage = $this->postGlDR($passageDebitRequest);
            $onCreditPassCash = $this->postGlCR($cashCreditRequest);
        }


        $stock = null;
        $history = null;
        if (isset($isEdit)) {
            $stock = stock::where('product_id', $isEdit)->first();
            $stock->updated_by = $userData->id;
            $stock->updated_on = Carbon::now();

            $history = stockHistory::where(['product_id' => $isEdit, 'stock_id' => $stock->id])->orderBy('created_at', 'desc')->first();
            $history->updated_by = $userData->id;
            $history->updated_on = Carbon::now();

        } else {
            $stock = new stock();
            $stock->created_by = $userData->id;
            $stock->created_on = Carbon::now();

            $history = new stockHistory();
            $history->created_by = $userData->id;
            $history->created_on = Carbon::now();
        }

        $history = new stockHistory();

        $stock->purchase_price = $request->purchase_price;
        $stock->selling_price = $request->selling_price;
        $stock->discount = $request->discount;
        $stock->product_id = $product->id;
        $stock->quantity = $request->quantity;
        $stock->min_quantity = $request->min_quantity;
        $stock->max_quantity = $request->max_quantity;
        $stock->institution_id = $userData->institution_id;
        $stock->stock_date = $request->date;
        $stock->manufactured_date = $request->manufactured_date;
        $stock->expiry_date = $request->expiry_date;
        $stock->branch_id = $userData->branch_id;
        $stock->user_id = $userData->id;
        $stock->status = $request->status;
        $stock->save();

        $history->purchase_price = $request->purchase_price;
        $history->selling_price = $request->selling_price;
        $history->discount = $request->discount;
        $history->product_id = $product->id;
        $history->stock_id = $stock->id;
        $history->quantity = $request->quantity;
        $history->min_quantity = $request->min_quantity;
        $history->max_quantity = $request->max_quantity;
        $history->institution_id = $userData->institution_id;
        $history->stock_date = $request->date;
        $history->manufactured_date = $request->manufactured_date;
        $history->expiry_date = $request->expiry_date;
        $history->branch_id = $userData->branch_id;
        $history->user_id = $userData->id;
        $history->status = $request->status;
        // $history->created_by = $userData->created_by ;
        // $history->created_on =now() ;
        $history->save();

        DB::commit();
        return $this->genericResponse(true, $message, 201, $product);
    }


    public function getProducts()
    {
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();

        $sqlString = "SELECT P.id,P.name, P.product_no, P.category_id, P.sub_category_id, P.manufacturer_id, P.supplier_id, P.measurement_unit_id, P.description,
        P.institution_id, P.user_id, P.status, P.created_by, P.updated_by, P.created_on, P.updated_on, P.created_at, P.updated_at, C.name AS category_name,
        S.name AS sub_category_name, M.name AS manufacturer, Q.name AS supplier, T.name AS unit, E.purchase_price, E.selling_price, E.discount, E.quantity,
        E.min_quantity, E.max_quantity, B.name AS branch_name, I.name AS institution_name,  P.tax_config
        FROM products P INNER JOIN product_categories C ON C.id = P.category_id
        LEFT JOIN product_sub_categories S ON P.sub_category_id = S.id
        LEFT JOIN manufacturers M ON  P.manufacturer_id = M.id
        LEFT JOIN suppliers Q ON P.supplier_id = Q.id
        LEFT JOIN measurement_units T ON P.measurement_unit_id = T.id
        INNER JOIN stocks E ON E.product_id = P.id
        INNER JOIN branches B ON B.id = E.branch_id
        INNER JOIN institutions I ON I.id =P.institution_id ";
        if ($isNotAdmin) {
            $sqlString .= " WHERE E.institution_id = $userData->institution_id AND E.branch_id = $userData->branch_id";
        }
        $sqlString .= " ORDER BY T.id DESC";
        $products = DB::select($sqlString);
        return $this->genericResponse(true, "Product list", 200, $products);
    }

    public function getProductDetails(Request $request)
    {
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();

        // $sqlString="SELECT P.id,P.name, P.product_no, P.category_id, P.sub_category_id, P.manufacturer_id, P.supplier_id, P.measurement_unit_id, P.description,
        // P.institution_id, P.user_id, P.status, P.created_by, P.type_id, P.gauge_id, E.stock_date, P.updated_by, P.created_on, P.updated_on, P.created_at, P.updated_at, C.name AS category_name,
        // S.name AS sub_category_name, M.name AS manufacturer, Q.name AS supplier, T.name AS unit, E.purchase_price, E.selling_price, E.discount, E.quantity,
        // E.min_quantity, E.id AS stock_id, E.max_quantity, B.name AS branch_name, I.name AS institution_name, P.tax_config
        // FROM products P INNER JOIN product_categories C ON C.id = P.category_id
        // LEFT JOIN product_sub_categories S ON P.sub_category_id = S.id
        // LEFT JOIN manufacturers M ON  P.manufacturer_id = M.id
        // LEFT JOIN suppliers Q ON P.supplier_id = Q.id
        // LEFT JOIN measurement_units T ON P.measurement_unit_id = T.id
        // INNER JOIN stocks E ON E.product_id = P.id
        // INNER JOIN branches B ON B.id = E.branch_id
        // INNER JOIN institutions I ON I.id =P.institution_id ";
        $sqlString = "SELECT P.id, P.name, P.product_no, P.category_id, P.sub_category_id, P.manufacturer_id, P.supplier_id, P.measurement_unit_id,
        P.description, P.institution_id, P.user_id, P.status, P.created_by, P.updated_by, P.created_on, P.updated_on, P.created_at,
        P.updated_at, P.type_id, P.gauge_id, P.ref_no, P.tax_config, S.purchase_price, S.selling_price, S.discount, S.quantity,
        S.min_quantity, S.max_quantity, S.stock_date, S.manufactured_date, S.expiry_date, C.name AS category_name, T.name AS type_name,
        K.name AS sub_category_name, G.name AS gauge_name, M.name AS manufacturer_name, M.website AS manufacturer_website,
        M.email AS manufacturer_email, M.phone_number AS manufacturer_phone_number,MC.name AS manufacturer_country, Q.name AS supplier_name,
        Q.website AS supplier_website, Q.email AS supplier_email, Q.phone_number AS supplier_phone_number, QC.name AS supplier_country,
        MU.name AS measurement_unit, CONCAT(U.first_name,' ',U.last_name, ' ', U.other_name) AS user_name, I.name AS institution_name,
        B.name AS branch_name
        FROM products P INNER JOIN stocks S ON S.product_id = P.id
        INNER JOIN product_categories C ON C.id = P.category_id
        INNER JOIN product_types T ON T.id = P.type_id
        LEFT JOIN product_sub_categories K ON K.id = P.sub_category_id
        LEFT JOIN product_gauges G ON G.id = P.gauge_id
        LEFT JOIN manufacturers M ON M.id = P.manufacturer_id
        LEFT JOIN country_refs MC ON M.country_id = MC.id
        LEFT JOIN suppliers Q ON Q.id = P.supplier_id
        LEFT JOIN country_refs QC ON Q.country_id = QC.id
        LEFT JOIN measurement_units MU ON MU.id = P.measurement_unit_id
        INNER JOIN users U ON U.id = P.user_id
        INNER JOIN institutions I ON I.id = P.institution_id
        LEFT JOIN branches B ON B.id = S.branch_id ";
        $sqlString .= " WHERE P.id = $request->id ";
        if ($isNotAdmin) {
            $sqlString .= " AND P.institution_id = $userData->institution_id AND S.branch_id = $userData->branch_id ";
        }
        $sqlString .= " ORDER BY P.id DESC";
        $product = DB::select($sqlString);

        return $this->genericResponse(true, "Product list", 200, $product);
    }

    public function downLoadProductPdfReport()
    {
        //TODO get products belonging to a store

        $products = Product::with(['stock', 'category', 'subCategory', 'productType', 'measurement'])->get();

        //return response()->json(['result'=>$products]);
        if (count($products) > 0) {
            Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
            $pdf = Pdf::loadView('product.reports', compact('products'));
            $pdf->setPaper('A4', 'patriot');
            $pdf->setBasePath(public_path());
            $pdf->setPaper('A4', 'patriot');
            $pdf->setBasePath(public_path());
            return $pdf->stream('product_report' . '.pdf');
        }
    }


    public function downLoadProductCsvReport()
    {
        //TODO get products belonging to a store

        return Excel::download(new ProductCsvExport, 'users.csv');

    }


    // public function getPro



}
