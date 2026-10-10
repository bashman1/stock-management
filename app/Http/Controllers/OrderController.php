<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductComponent;
use App\Models\stock;
use App\Models\Branch;
use App\Models\GlAccounts;
use App\Models\GlBalances;
use App\Models\CntrlParameter;
use App\Models\GlHistory;
use App\Models\Institution;
use App\Models\TempSale;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Services\EfrisApiService;
use Illuminate\Support\Facades\Log;

use charlieuki\ReceiptPrinter\ReceiptPrinter as ReceiptPrinter;

class OrderController extends Controller
{

    protected EfrisApiService $efrisService;
    public function __construct(EfrisApiService $efrisService)
    {
        $this->efrisService = $efrisService;
    }
    public function createOrder(Request $request)
    {
        // Credit-limit enforcement: block a CREDIT sale that would push the
        // customer past their limit (nothing is created on rejection).
        if ($request->payment_method == 'CREDIT') {
            $custId = $request->customer_id ?? $request->customerId;
            if ($custId) {
                $customer = \App\Models\Customer::find($custId);
                $limit = (float) ($customer->credit_limit ?? 0);
                if ($customer && $limit > 0) {
                    $outstanding = (float) \App\Models\CustomerReceivable::where(['customer_id' => $custId, 'status' => 'Active'])
                        ->sum(DB::raw('amount - amount_paid'));
                    if ($outstanding + (float) $request->total > $limit) {
                        return $this->genericResponse(false,
                            "Credit limit exceeded — outstanding " . number_format($outstanding) . " + this sale " . number_format((float) $request->total) . " exceeds the limit " . number_format($limit),
                            400, null, "createOrder", $request);
                    }
                }
            }
        }

        $response = $this->newSale($request);

        return $this->genericResponse(true, "Order submitted successfully", 201, $response, "createOrder", $request);
    }

    public function newSale($request)
    {
        $institutionData = $this->getUserInstitution();
        DB::beginTransaction();
        $userData = Auth::user();
        $order = new Order();
        $ref = $this->generateTranRef() ?? '';
        $order->ref_no = $ref;
        $order->receipt_no = str_replace('TRN', '', $ref);
        $order->tran_id = $this->generateUuid();
        $order->item_count = $request->itemCount;
        $order->total =  (float) $request->total;
        $order->discount =  (float) $request->discount;
        $order->amount_paid =  (float) $request->amountPaid;
        $order->user_id = $userData->id;
        $order->customer_id = $request->customerId;
        $order->tran_date = $request->tranDate;
        $order->status = $request->status;
        $order->customer_id = $request->customer_id;
        $order->address = $request->address;
        // Customer's own (hand-written / external) receipt number, if they had one.
        // ?? null because approveBatchSales() calls newSale() with a stdClass that
        // has no such property.
        $order->physical_receipt_no = $request->physical_receipt_no ?? null;
        $order->payment_method = $request->payment_method;
        $order->payment_status = $request->Paid;
        $order->institution_id = $userData->institution_id;
        $order->branch_id = $userData->branch_id;
        $order->created_by = $userData->id;
        $order->created_on = now();
        $order->save();


        $institution = Institution::find($userData->institution_id);
        if (!isset($institution)) {
            return $this->genericResponse(false, "Institution not found", 400, $institution);
        }

        $totalVATPayable = 0;
        if ($institution->is_tax_enabled) {
            if (isset($request->vatEx)) {
                $totalVATPayable = $totalVATPayable + $request->vatEx;
            }
            if (isset($request->vatInc)) {
                $totalVATPayable = $totalVATPayable + $request->vatInc;
            }
        }

        $branch = Branch::where(['id' => $userData->branch_id, 'institution_id' => $userData->institution_id])->first();


        $cl = null;
        if (isset($request->payment_method)) {
            if ($request->payment_method == "BANK") {
                $cl = CntrlParameter::where(['param_cd' => 'CGL', 'institution_id' => $userData->institution_id])->first();
            } elseif ($request->payment_method == "CREDIT") {
                $cl = CntrlParameter::where(['param_cd' => 'AR', 'institution_id' => $userData->institution_id])->first();
            } else {
                $cl = CntrlParameter::where(['param_cd' => 'CL', 'institution_id' => $userData->institution_id])->first();
            }
        } else {
            $cl = CntrlParameter::where(['param_cd' => 'CL', 'institution_id' => $userData->institution_id])->first();
        }
        //        $cl = CntrlParameter::where(['param_cd'=>'CL', 'institution_id'=>$userData->institution_id])->first();
        $sti = CntrlParameter::where(['param_cd' => 'SL', 'institution_id' => $userData->institution_id])->first();
        $vat = CntrlParameter::where(['param_cd' => 'TAX', 'institution_id' => $userData->institution_id])->first();

        $cgl = str_replace('***', $branch->code, $cl->param_value);
        $cash = GlAccounts::where('acct_no', $cgl)->first();

        $sgl = str_replace('***', $branch->code, $sti->param_value);
        $stock = GlAccounts::where('acct_no', $sgl)->first();

        $vatGl = str_replace('***', $branch->code, $vat->param_value);
        $vatTax = GlAccounts::where('acct_no', $vatGl)->first();


        /**stock and goods for sales */
        $gfs =  CntrlParameter::where(['param_cd' => 'GFS', 'institution_id' => $userData->institution_id])->first();
        $stIn =  CntrlParameter::where(['param_cd' => 'STI', 'institution_id' => $userData->institution_id])->first();

        $gfsGl = str_replace('***', $branch->code, $gfs->param_value);
        $stInGl = str_replace('***', $branch->code, $stIn->param_value);
        $gfsGlType = GlAccounts::where('acct_no', $gfsGl)->first();
        $stInGlType = GlAccounts::where('acct_no', $stInGl)->first();

        // Capture the resolved accounts into clearly-named vars (the $stock var is
        // reused for the per-item inventory row inside the loop, so grab the Sales
        // account type now before it is clobbered).
        $cashAcctNo = $cgl;
        $cashAcctType = $cash->acct_type;
        $salesAcctNo = $sgl;
        $salesAcctType = $stock->acct_type;   // INCOME (main Sales / SL)
        $cogsAcctNo = $gfsGl;
        $cogsAcctType = $gfsGlType->acct_type; // EXPENSE (COGS)
        $invAcctNo = $stInGl;
        $invAcctType = $stInGlType->acct_type; // ASSET (Inventory)

        // Dedicated revenue accounts for service / digital products (fall back to
        // the main Sales account if a control parameter is missing).
        $svrParam = CntrlParameter::where(['param_cd' => 'SVR', 'institution_id' => $userData->institution_id])->first();
        $dgsParam = CntrlParameter::where(['param_cd' => 'DGS', 'institution_id' => $userData->institution_id])->first();
        $svrAcctNo = $svrParam ? str_replace('***', $branch->code, $svrParam->param_value) : $salesAcctNo;
        $dgsAcctNo = $dgsParam ? str_replace('***', $branch->code, $dgsParam->param_value) : $salesAcctNo;
        $svrAcct = GlAccounts::where('acct_no', $svrAcctNo)->first();
        $dgsAcct = GlAccounts::where('acct_no', $dgsAcctNo)->first();
        $svrAcctType = $svrAcct->acct_type ?? $salesAcctType;
        $dgsAcctType = $dgsAcct->acct_type ?? $salesAcctType;

        // Total revenue to recognise (sale total net of VAT) is split across items
        // by their gross share, then credited to each item's revenue account.
        $totalRevenue = (float) $request->total - $totalVATPayable;
        $grossSum = 0;
        foreach ($request->items as $gv) {
            $grossSum += (float) $gv['price'] * (float) $gv['quantity'];
        }

        $tran = (array)[
            "acct_no" => $sgl,
            "acct_type" => $stock->acct_type,
            "contra_acct_no" => $cgl,
            "contra_acct_type" => $cash->acct_type,
            "description" => "selling products receipt no $order->receipt_no and  transaction id $order->tran_id " . date('Y-m-d H:i:s'),
            "dr_cr_ind" => "DR/CR",
            "tran_amount" => $request->total,
            "reversal_flag" => 'N',
            "tran_date" => isset($request->tranDate) ? $request->tranDate : now(),
            "tran_cd" => 'STI',
            "tran_id" => $this->generateUuid(),
            "status" => 'Active',
            "institution_id" => $userData->institution_id,
            "branch_id" => $userData->branch_id,
            "created_by" => $userData->id,
            "created_on" => now(),
            // "member_id"=>null,
            // "product_id"=>$product->id,
            // "stock_id"=>null,
            "ext_ref" => $ref,
            "tran" => "Selling Product",
            // "reversal_reason"=>null,
            // "reversed_by"=>null,
            // "reversed_on"=>null,
        ];

        $postTran = $this->postTransaction($tran);
        $creditRequest = (object)[
            "acct_no" => $sgl,
            "acct_type" => $stock->acct_type,
            "tran_amt" => ($postTran->tran_amount ?? 0) - $totalVATPayable,
            "reversal_flag" => 'N',
            "description" => $postTran->description ?? '',
            "transaction_date" => $postTran->tran_date ?? now(),
            "contra_acct_no" => $cgl,
            "contra_acct_type" => $cash->acct_type,
            "tran_type" => 'STOCK IN',
            "tran_cd" => 'STO',
            "tran_id" => $postTran->tran_id ?? null,
            "institution_id" => $userData->institution_id,
            "branch_id" => $userData->branch_id,
            "created_by" => $userData->id,
            "status" => 'Active',
        ];

        $debitRequest = (object)[
            "acct_no" => $cgl,
            "acct_type" => $cash->acct_type,
            "tran_amt" => $postTran->tran_amount ?? 0,
            "reversal_flag" => 'N',
            "description" => $postTran->description ?? '',
            "transaction_date" => $postTran->tran_date ?? now(),
            "contra_acct_no" => $sgl,
            "contra_acct_type" => $stock->acct_type,
            "tran_type" => 'STOCK OUT',
            "tran_cd" => 'STO',
            "tran_id" => $postTran->tran_id ?? null,
            "institution_id" => $userData->institution_id,
            "branch_id" => $userData->branch_id,
            "created_by" => $userData->id,
            "status" => 'Active',
        ];

        $taxCreditRequest = (object)[
            "acct_no" => $vatTax->acct_no,
            "acct_type" => $vatTax->acct_type,
            "tran_amt" => $totalVATPayable,
            "reversal_flag" => 'N',
            "description" => $postTran->description ?? '',
            "transaction_date" => $postTran->tran_date ?? now(),
            "contra_acct_no" => $cgl,
            "contra_acct_type" => $cash->acct_type,
            "tran_type" => 'STOCK OUT',
            "tran_cd" => 'STO',
            "tran_id" => $postTran->tran_id ?? null,
            "institution_id" => $userData->institution_id,
            "branch_id" => $userData->branch_id,
            "created_by" => $userData->id,
            "status" => 'Active',
        ];
        // Debit Cash/Bank/AR for the full order total. The contra credit is no
        // longer a single Sales posting — revenue is credited per item below to
        // each item's own revenue account (Sales / Service Revenue / Digital Sales).
        $debit = $this->postGlDR($debitRequest);

        // Builds a GL Dr/Cr request object for a posting in this sale.
        $mkGl = function ($acctNo, $acctType, $amt, $contraNo, $contraType) use ($postTran, $userData) {
            return (object) [
                "acct_no" => $acctNo,
                "acct_type" => $acctType,
                "tran_amt" => $amt,
                "reversal_flag" => 'N',
                "description" => $postTran->description ?? '',
                "transaction_date" => $postTran->tran_date ?? now(),
                "contra_acct_no" => $contraNo,
                "contra_acct_type" => $contraType,
                "tran_type" => 'STOCK OUT',
                "tran_cd" => 'STO',
                "tran_id" => $postTran->tran_id ?? null,
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "status" => 'Active',
            ];
        };



        if ($request->payment_method == "CREDIT") {
            $receivable = (array)[
                "ref_no" => $order->ref_no,
                "receipt_no" => $order->receipt_no,
                "tran_id" => $order->tran_id,
                "customer_id" => $request->customer_id,
                "amount" =>  (float) $request->total,
                "amount_paid" => 0,
                "status" => "Active",
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "created_on" => Carbon::now()
            ];
            $this->createReceivable($receivable);
        }

        if ($institution->is_tax_enabled) {
            $VATcredit = $this->postGlCR($taxCreditRequest);
        }

        $efrisProductDetails = [
            "item" => "apple seeds",
            "itemCode" => "A001",
            "qty" => "2",
            "unitOfMeasure" => "KGM",
            "unitPrice" => "3500",
            "total" => "7000",
            "taxRate" => "0",
            "tax" => "0",
            "discountTotal" => "",
            "discountTaxRate" => "",
            "orderNumber" => "0",
            "discountFlag" => "2",
            "deemedFlag" => "2",
            "categoryId" => "",
            "categoryName" => "",
            "goodsCategoryId" => "95141708",
            "goodsCategoryName" => "",
            "exciseFlag" => "2",
            "exciseRate" => "",
            "exciseRule" => "",
            "exciseTax" => "",
            "pack" => "",
            "stick" => "",
            "exciseUnit" => "",
            "exciseCurrency" => "",
            "exciseRateName" => "",
            "isExempt" => "102",
            "isZeroRate" => "102"
        ];

        $efrisOrderDetailsList = [];

        $itemIndex = 0;
        $itemCount = count($request->items);
        $allocatedRevenue = 0;
        foreach ($request->items as $value) {
            $itemIndex++;
            $product = Product::find($value['id']);
            $nature = $product->nature ?? 'STOCK';

            $stock = stock::where(['product_id' => $value['id'], 'branch_id' => $userData->branch_id])->first();
            $items = new OrderItem();
            $items->order_id = $order->id;
            $items->product_id = $value['id'];
            // selected.weight carries the unit's conversion_factor (base units per
            // one of the chosen unit). qty stays in the chosen unit (for receipts/
            // reports); base_qty is the base-unit equivalent (for stock/EFRIS).
            $conversion = (float) $value['selected']['weight'];
            $chosenQty = (float) $value['quantity'];
            $baseQty = $chosenQty * $conversion;
            // per-chosen-unit cost = base purchase price * units-of-base-per-chosen
            $chosenPurchasePrice = (float) ($stock->purchase_price ?? 0) * $conversion;
            $items->qty =  $chosenQty;
            $items->base_qty =  $baseQty;
            $items->selling_price =  (float) $value['price'];
            $items->purchase_price =  $chosenPurchasePrice;
            $items->status = $request->status;
            $items->institution_id = $userData->institution_id;
            $items->branch_id = $userData->branch_id;
            $items->created_by = $userData->id;
            $items->measurement_unit_id = $value['selected']['unit_id'];
            $items->created_on = now();

            // --- Recognise revenue to this item's account (Sales / Service / Digital) ---
            $gross = (float) $value['price'] * $chosenQty;
            if ($itemIndex === $itemCount) {
                $itemNet = round($totalRevenue - $allocatedRevenue, 2); // remainder avoids rounding drift
            } else {
                $itemNet = $grossSum > 0 ? round($totalRevenue * $gross / $grossSum, 2) : 0;
                $allocatedRevenue += $itemNet;
            }
            if ($nature === 'SERVICE') {
                $revAcctNo = $svrAcctNo;
                $revAcctType = $svrAcctType;
            } elseif ($nature === 'DIGITAL') {
                $revAcctNo = $dgsAcctNo;
                $revAcctType = $dgsAcctType;
            } else {
                $revAcctNo = $salesAcctNo;
                $revAcctType = $salesAcctType;
            }
            if ($itemNet != 0) {
                $this->postGlCR($mkGl($revAcctNo, $revAcctType, $itemNet, $cashAcctNo, $cashAcctType));
            }

            // --- Cost of sales + stock movement, by nature ---
            if ($nature === 'STOCK' && isset($stock)) {
                // stock is held in base units, so deduct the converted quantity
                $stock->quantity =  (float) $stock->quantity - $baseQty;
                $stock->save();
                // keep the warehouse sub-ledger in step (default warehouse)
                $this->adjustWarehouseStock($value['id'], $userData->branch_id, -$baseQty);
                // COGS comes from consuming cost layers by the product's valuation method
                $cost = $this->issueStock($value['id'], $userData->branch_id, $baseQty, $userData, 'SALE', $ref, $postTran->tran_id ?? null);
                if ($cost != 0) {
                    $this->postGlDR($mkGl($cogsAcctNo, $cogsAcctType, $cost, $invAcctNo, $invAcctType));
                    $this->postGlCR($mkGl($invAcctNo, $invAcctType, $cost, $cogsAcctNo, $cogsAcctType));
                }
            } elseif ($nature === 'BUNDLE') {
                // a bundle decrements each component's stock; COGS = sum of components
                $components = ProductComponent::where('bundle_product_id', $product->id)->get();
                foreach ($components as $comp) {
                    $compStock = stock::where(['product_id' => $comp->component_product_id, 'branch_id' => $userData->branch_id])->first();
                    if (!isset($compStock)) {
                        continue;
                    }
                    $compBaseQty = (float) $comp->quantity * $chosenQty; // component base units per bundle x bundles sold
                    $compStock->quantity = (float) $compStock->quantity - $compBaseQty;
                    $compStock->save();
                    $this->adjustWarehouseStock($comp->component_product_id, $userData->branch_id, -$compBaseQty);
                    $compCost = $this->issueStock($comp->component_product_id, $userData->branch_id, $compBaseQty, $userData, 'SALE', $ref, $postTran->tran_id ?? null);
                    if ($compCost != 0) {
                        $this->postGlDR($mkGl($cogsAcctNo, $cogsAcctType, $compCost, $invAcctNo, $invAcctType));
                        $this->postGlCR($mkGl($invAcctNo, $invAcctType, $compCost, $cogsAcctNo, $cogsAcctType));
                    }
                }
            }
            // NON_STOCK / SERVICE / DIGITAL: revenue only, no stock movement or COGS.

            // setting efris details — EFRIS records goods in the base measure unit,
            // so send base quantity with the per-base-unit price.
            $baseUnitPrice = $conversion > 0 ? ((float) $value['price'] / $conversion) : (float) $value['price'];
            $efrisProductDetails['item'] = $product->name;
            $efrisProductDetails['itemCode'] = $product->ref_no;
            $efrisProductDetails['qty'] = $baseQty;
            $efrisProductDetails['unitPrice'] = $baseUnitPrice;
            $efrisProductDetails['total'] = $efrisProductDetails['qty'] * $efrisProductDetails['unitPrice'];

            $efrisOrderDetailsList[] = $efrisProductDetails;

            $items->save();
        }

        if (isset($institutionData->is_tax_enabled) && $institutionData->efris_enabled) {
            $efrisResponse = $this->efrisCreateSalesReceipt($order->ref_no, $order->tran_date, $efrisOrderDetailsList);
            if ($request->payment_method == "CREDIT") {
                $creditNote = $this->createCreditNote($efrisResponse, $request->tran_date, $efrisOrderDetailsList);
            }

            $order->efris_response = $efrisResponse;
            $order->save();
        }


        DB::commit();
        return $order;
    }

    public function getOrders(Request $request)
    {
        $rowsPerPage = $request->integer('rows_per_page', 15);
        $page = $request->integer('page', 1);

        // $startDate = $request->filled('start_date') ? $request->date('start_date') : null;
        // $endDate = $request->filled('end_date')? $request->date('end_date'): null;
        // $searchKeyword = $request->string('search');

        $startDate = $this->safeDate($request->input('start_date'));
        $endDate = $this->safeDate($request->input('end_date'));

        log::info("Fetching orders with filters - Start Date: $startDate, End Date: $endDate");


        $searchKeyword = $request->input('search');

        if ($searchKeyword === 'null') {
            $searchKeyword = null;
        }

        $orders = $this->getOrdersList($rowsPerPage, $page, $startDate, $endDate, $searchKeyword);
        return $this->genericResponse(true, "Orders fetched successfully", 200, $orders, "getOrders", []);
    }

    public function getOrdersList($perPage, $page, $startDate, $endDate, $searchKeyword)
    {
        $userData = Auth::user();
        $isNotAdmin = $this->isNotAdmin();

        log::info("Fetching orders with filters - Start Date: $startDate, End Date: $endDate, Search Keyword: $searchKeyword, User ID: $userData->id");

        $queryString = "SELECT O.id, O.ref_no, O.receipt_no, O.physical_receipt_no, O.tran_id, O.item_count, O.total,
        O.discount, O.amount_paid, O.user_id, O.customer_id, O.tran_date,
        O.status, O.payment_status, O.institution_id, O.branch_id, O.created_on,
        O.created_at, O.updated_at FROM orders O";

        $totalSql = "SELECT  COUNT(O.id) AS total FROM orders O";

        if ($isNotAdmin) {
            $queryString .= " WHERE O.institution_id = $userData->institution_id AND O.branch_id=$userData->branch_id AND O.status='Active' ";
            $totalSql .= " WHERE O.institution_id = $userData->institution_id AND O.branch_id=$userData->branch_id AND O.status='Active' ";
        } else {
            $queryString .= " WHERE O.status='Active' ";
            $totalSql .= " WHERE O.status='Active' ";
        }

        if (!empty(trim($startDate))) {
            $queryString .= " AND O.tran_date::date >= '$startDate'";
            $totalSql .= " AND O.tran_date::date >= '$startDate'";
        }

        if (!empty(trim($endDate))) {
            $queryString .= " AND O.tran_date::date  <= '$endDate'";
            $totalSql .= " AND O.tran_date::date <= '$endDate'";
        }
        log::info("keyword after trim: '" . $searchKeyword . "'");
        if (!empty(trim($searchKeyword))) {
            $searchClause = " AND (O.ref_no ILIKE '%" . $searchKeyword . "%' OR O.receipt_no ILIKE '%" . $searchKeyword . "%'"
                . " OR O.physical_receipt_no ILIKE '%" . $searchKeyword . "%')";
            $queryString .= $searchClause;
            $totalSql .= $searchClause;
        }

        $queryString .= " ORDER BY O.id DESC";


        // $orders = DB::select($queryString);
        $orders = $this->paginate($perPage, $totalSql, $queryString, $page);
        return $orders;
    }





    public function getOderDetails($orderId)
    {
        $queryString = " SELECT O.id, O.order_id, O.product_id,O.qty, O.status,O.institution_id, O.branch_id, O.created_at, P.name,
        P.product_no, COALESCE(O.selling_price ,S.selling_price)  AS price, I.name AS institution_name, B.name AS branch_name, Q.ref_no,
        Q.receipt_no, Q.physical_receipt_no, Q.tran_id, Q.item_count, Q.total, Q.discount, Q.amount_paid, Q.tran_date, Q.payment_status, Q.efris_response,
        CONCAT(U.first_name,' ',U.last_name,' ',U.other_name) AS user_name, B.address AS branch_address,
		C.name AS customer_name, X.name AS unit, I.p_o_box,I.address AS institution_address FROM order_items O
        INNER JOIN products P ON P.id = O.product_id
        INNER JOIN stocks S  ON S.product_id = P.id
        INNER JOIN institutions I ON I.id =O.institution_id
        LEFT JOIN branches B ON B.id = O.branch_id
        INNER JOIN orders Q ON Q.id = O.order_id
		LEFT JOIN customers C ON C.id = Q.customer_id
        LEFT JOIN measurement_units X ON X.id = O.measurement_unit_id
        INNER JOIN users U ON Q.user_id = U.id WHERE  O.order_id = $orderId";
        $orderDetails = DB::select($queryString);
        return $this->genericResponse(true, "Order details fetched successfully", 200, $orderDetails, "getOderDetails", $orderId);
    }

    public function approveBatchSales(Request $request)
    {
        DB::beginTransaction();
        $tempArray = [];
        $total = 0;
        $stockDt = null;
        foreach ($request->sales as $sale) {
            $product = Product::where(["ref_no" => $sale["ref_no"], "institution_id" => $sale["institution_id"]])->first();
            $stock = stock::where(["product_id" => $product->id, "branch_id" => $sale["branch_id"]])->first();
            $sum = $sale["quantity"] * $stock->selling_price;
            $total = $total + (float)$sum;
            $tempArray[] = ["id" => $product->id, "quantity" => $sale["quantity"], "status" => "Active"];

            $stockDt = $sale["stock_date"];

            $updateSale = TempSale::find($sale["id"]);
            $updateSale->status = "Active";
            $updateSale->save();
        }

        $newSale = (object)[
            "itemCount" => count($tempArray),
            "total" => $total,
            "discount" => 0,
            "amountPaid" => $total,
            "customerId" => null,
            "tranDate" => $stockDt,
            "status" => "Active",
            "Paid" => "Paid",
            "vatEx" => 0,
            "vatInc" => 0,
            "items" => $tempArray,
        ];
        $response = $this->newSale($newSale);
        DB::commit();
        return $this->genericResponse(true, "Approved Successfully", 200,  $response, "approveBatchSales", $request);
    }


    public function printReceipt(Request $request)
    {

        $userData = $this->getUserLogin();
        $institution = Institution::find($userData->institution_id);
        // Set params
        $mid = $institution->ref_no;
        $store_name = $institution->name;
        $store_address = $institution->address;
        $store_phone = '1234567890';
        $store_email = 'yourmart@email.com';
        $store_website = 'yourmart.com';
        // $tax_percentage = 18;
        $transaction_id = '';




        $rawItems = $request->items; // or json_decode($request->getContent(), true)

        // Initialize formatted items array
        $items = [];

        foreach ($rawItems as $item) {
            $transaction_id = $item['receipt_no'];
            // Convert strings to integers/floats if needed
            $quantity = (float) $item['qty'];
            $price = (float) $item['price'];
            $subTotal = $quantity * $price;

            $items[] = [
                'name' => $item['name'] . (isset($item['unit']) ? ' (' . $item['unit'] . ')' : ''),
                // 'name'      => $item['name'] . (isset($item['unit']) ? ' ' . $item['unit'] : ''),
                'qty'       => $quantity, // e.g., "1 Bag"
                'price'     => $price,
                'sub_total' => $subTotal,
            ];
        }


        // Init printer
        $printer = new ReceiptPrinter;
        $printer->init(
            config('receiptprinter.connector_type'),
            config('receiptprinter.connector_descriptor')
        );

        $printer->setCurrency('UGX: ');

        // Set store info
        $printer->setStore($mid, $store_name, $store_address, $store_phone, $store_email, $store_website);

        // Add items
        foreach ($items as $item) {
            $printer->addItem(
                $item['name'],
                $item['qty'],
                $item['price'],
            );
        }
        // Set tax
        $printer->setTax(0);

        // Calculate total
        $printer->calculateSubTotal();
        $printer->calculateGrandTotal();

        // Set transaction ID
        $printer->setTransactionID($transaction_id);

        // Set qr code
        $printer->setQRcode([
            'tid' => $transaction_id,
        ]);

        // Print receipt
        $printer->printReceipt();
    }



    public function deleteOrder(Request $request)
    {
        DB::beginTransaction();
        $order = Order::find($request->id);
        if (!isset($order)) {
            return $this->genericResponse(false, "Order not found", 400, $order, "deleteOrder", $request->id);
        }
        // idempotency: never reverse an already-reversed order (prevents cascade)
        if ($order->status === "Deleted") {
            DB::rollBack();
            return $this->genericResponse(false, "Order has already been reversed", 400, $order, "deleteOrder", $request->id);
        }
        $order->status = "Deleted";
        $order->updated_by = Auth::user()->id;
        $order->updated_on = now();
        $order->save();


        // updating the transactions
        $orderTransaction = Transaction::where(['ext_ref' => $order->ref_no])->first();

        if (!isset($orderTransaction)) {
            return $this->genericResponse(false, "Transaction not found", 400, $orderTransaction, "deleteOrder", $request->id);
        }

        if (isset($orderTransaction)) {
            $orderTransaction->status = "Deleted";
            $orderTransaction->reversal_flag = "Y";
            $orderTransaction->reversal_reason = "Order deleted";
            $orderTransaction->reversed_by = Auth::user()->id;
            $orderTransaction->reversed_on = now();
            $orderTransaction->updated_by = Auth::user()->id;
            $orderTransaction->updated_on = now();
            $orderTransaction->save();
        }

        // Reverse the GL effect of this transaction's LIVE entries (flag-and-exclude):
        // undo each posting's impact on gl_balances using the account's TRUE acct_type
        // (looked up from gl_accounts, since gl_histories has no acct_type), then mark
        // the row reversed. No opposite history rows are created, so re-deletes cannot
        // cascade and the wrong-direction acct_type bug is avoided.
        $glRows = GlHistory::where(['tran_id' => $orderTransaction->tran_id, 'reversal_flag' => 'N'])->get();
        foreach ($glRows as $item) {
            $glAccount = GlAccounts::where('acct_no', $item->acct_no)->first();
            $acctType = $glAccount?->acct_type;
            $balance = GlBalances::where('acct_no', $item->acct_no)->first();
            if (isset($balance)) {
                $isDebitNormal = ($acctType == 'ASSET' || $acctType == 'EXPENSE');
                $amt = (float) $item->tran_amount;
                // apply the OPPOSITE of the original posting's effect
                if ($item->dr_cr_ind == 'Dr') {
                    $balance->balance = $isDebitNormal ? $balance->balance - $amt : $balance->balance + $amt;
                } else { // original was a credit
                    $balance->balance = $isDebitNormal ? $balance->balance + $amt : $balance->balance - $amt;
                }
                $balance->updated_on = now();
                $balance->save();
            }

            $item->status = "Deleted";
            $item->reversal_flag = "Y";
            $item->updated_by = Auth::user()->id;
            $item->updated_on = now();
            $item->save();
        }


        // updating the receivable in case of credit sales


        // updating the order items
        $orderItems = OrderItem::where(['order_id' => $request->id])->get();
        foreach ($orderItems as $item) {
            $item->status = "Deleted";
            $item->updated_by = Auth::user()->id;
            // $item->updated_by = optional(auth()->user())->id;
            // $item->updated_by = auth()->user()->id;
            $item->updated_on = now();
            $item->save();

            // updating the stock
            $stock = stock::where(['product_id' => $item->product_id, 'branch_id' => $order->branch_id])->first();
            if (isset($stock)) {
                // restore in base units (fall back to qty for pre-migration rows)
                $restoreQty = ($item->base_qty ?? $item->qty);
                $stock->quantity = $stock->quantity + $restoreQty;
                $stock->save();
                $this->adjustWarehouseStock($item->product_id, $order->branch_id, $restoreQty);
            }
        }

        // restore the cost layers this sale consumed (exact issued cost per product,
        // covering both direct STOCK items and bundle components)
        $outMovements = \App\Models\InventoryMovement::where([
            'tran_id' => $orderTransaction->tran_id, 'direction' => 'OUT',
        ])->get();
        foreach ($outMovements as $mv) {
            $unit = (float) $mv->quantity > 0 ? (float) $mv->total_cost / (float) $mv->quantity : (float) $mv->unit_cost;
            $this->receiveStock($mv->product_id, $order->branch_id, (float) $mv->quantity, $unit, 'RETURN', $order->ref_no, now(), Auth::user(), $orderTransaction->tran_id);
        }

        DB::commit();
        return $this->genericResponse(true, "Sale reversed successfully", 200, $order, "deleteOrder", $request->id);
    }


    public function efrisCreateSalesReceipt($refNo, $date, $efrisProductDetails,)
    {
        try {
            $institutionData = $this->getUserInstitution();
            $user = $this->getUserLogin();
            $payload = [
                "referenceNo" => (string) $refNo,
                "invoiceDiscountTotal" => "",
                "basicInformation" => [
                    "invoiceNo" => "",
                    "antifakeCode" => "",
                    "issuedDate" => Carbon::parse($date)->format('Y-m-d'),
                    "dataSource" => "103",
                    "oriInvoiceId" => "",
                    "operator" => $user->first_name . ' ' . $user->last_name,
                    "currency" => "UGX",
                    "invoiceType" => "1",        // 2 = Tax Invoice, 1 = Receipt
                    "invoiceKind" => "2",        // 2 = Normal receipt
                    "invoiceIndustryCode" => "101",
                    "isBatch" => ""
                ],
                "buyerDetails" => [
                    "buyerTin" => "",
                    "buyerNinBrn" => "",
                    "buyerPassportNum" => "",
                    "buyerLegalName" => "Masaka ltd",
                    "buyerBusinessName" => "Masaka ltd",
                    "buyerAddress" => "uganda",
                    "buyerEmail" => "",
                    "buyerMobilePhone" => "0705412894",
                    "buyerLinePhone" => "0705412894",
                    "buyerPlaceOfBusi" => "",
                    "buyerType" => "1",
                    "buyerCitizenship" => "",
                    "buyerSector" => "",
                    "buyerReferenceNo" => ""
                ],
                "goodsDetails" => $efrisProductDetails
            ];

            // log::info($payload);

            // $response = $efrisApiService->request('GET', '1033614630/auth/server-time', [],[]);
            // $response = $this->efrisService->request('POST', $institutionData->tin . '/invoices/create', [], $payload);
            $response = $this->efrisService->queueRequest('POST', $institutionData->tin . '/invoices/create', [], $payload, $institutionData);

            // if ($institutionData->)


            log::info("**************** EFRIS RESPONSE => Creating receipt ****************");
            log::info($response);
            return $response['res'];
        } catch (\Throwable $th) {
            throw $th;
        }
    }



    public function createCreditNote($res, $date, $efrisProductDetails)
    {
        try {
            $institutionData = $this->getUserInstitution();

            log::info("**************** EFRIS RESPONSE => Creating credit note ****************");
            log::info($res);
            log::info("**************** EFRIS RESPONSE => Creating credit note ****************");

            $payload = [
                "sellersReferenceNo" => "",
                "oriInvoiceId" => $res['data']['basicInformation']['invoiceId'],
                "oriInvoiceNo" => $res['data']['basicInformation']['invoiceNo'],
                "reasonCode" => "102",
                "reason" => "",
                "applicationTime" => Carbon::parse($date)->format('Y-m-d H:i:s'),
                "currency" => "UGX",
                "remarks" => "Orders on credit",
                "goodsDetails" => $efrisProductDetails,
                // "goodsDetails" => [
                //     [
                //         "isExempt" => "102",
                //         "isZeroRate" => "102",
                //         "item" => "apple seeds",
                //         "itemCode" => "A001",
                //         "qty" => "2",
                //         "unitOfMeasure" => "KGM",
                //         "unitPrice" => "3500",
                //         "total" => "7000",
                //         "taxRate" => "0.18",
                //         "tax" => "5000",
                //         "discountTotal" => "-2000",
                //         "orderNumber" => "0",
                //         "discountFlag" => "1",
                //         "deemedFlag" => "2",
                //         "categoryId" => "",
                //         "categoryName" => "",
                //         "goodsCategoryId" => "95141708",
                //         "goodsCategoryName" => "",
                //         "exciseFlag" => "2",
                //         "exciseRate" => "",
                //         "exciseRule" => "",
                //         "exciseTax" => "",
                //         "pack" => "",
                //         "stick" => "",
                //         "exciseUnit" => "",
                //         "exciseCurrency" => "",
                //         "exciseRateName" => ""
                //     ]
                // ]
            ];

            $res = $this->efrisService->request('POST', $institutionData->tin . '/credit-notes/create', [], $payload);

            return $res;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
