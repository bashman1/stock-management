<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\stock;
use App\Models\Branch;
use App\Models\GlAccounts;
use App\Models\CntrlParameter;
use App\Models\Institution;
use App\Models\TempSale;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use charlieuki\ReceiptPrinter\ReceiptPrinter as ReceiptPrinter;

class OrderController extends Controller
{
    public function createOrder(Request $request){
        $response = $this->newSale($request);

        return $this->genericResponse(true, "Order submitted successfully", 201, $response, "createOrder", $request);
    }

    public function newSale($request){
        DB::beginTransaction();
        $userData = auth()->user();
        $order = new Order();
        $ref=$this->generateTranRef();
        $order->ref_no = $ref;
        $order->receipt_no = str_replace('TRN', '', $ref) ;
        $order->tran_id = $this->generateUuid() ;
        $order->item_count = $request->itemCount ;
        $order->total =  (double) $request->total ;
        $order->discount =  (double) $request->discount ;
        $order->amount_paid =  (double) $request->amountPaid ;
        $order->user_id = $userData->id ;
        $order->customer_id = $request->customerId;
        $order->tran_date = $request->tranDate ;
        $order->status = $request->status ;
        $order->customer_id = $request->customer_id ;
        $order->address = $request->address ;
        $order->payment_method = $request->payment_method ;
        $order->payment_status = $request->Paid ;
        $order->institution_id = $userData->institution_id;
        $order->branch_id = $userData->branch_id ;
        $order->created_by = $userData->id ;
        $order->created_on = now() ;
        $order->save();


        $institution = Institution::find($userData->institution_id);
        if (!isset($institution)){
            return $this->genericResponse(false, "Institution not found", 400, $institution);
        }

        $totalVATPayable = 0;
        if($institution->is_tax_enabled){
            if(isset($request->vatEx)){
                $totalVATPayable = $totalVATPayable+$request->vatEx;
            }
            if(isset($request->vatInc)){
                $totalVATPayable = $totalVATPayable+$request->vatInc;
            }
        }

        $branch = Branch::where(['id'=>$userData->branch_id, 'institution_id'=>$userData->institution_id])->first();


        $cl = null;
        if (isset($request->payment_method)){
            if ($request->payment_method == "BANK"){
                $cl = CntrlParameter::where(['param_cd' => 'CGL', 'institution_id' => $userData->institution_id])->first();
            }elseif ($request->payment_method == "CREDIT"){
                $cl = CntrlParameter::where(['param_cd' => 'AR', 'institution_id' => $userData->institution_id])->first();
            }else{
                $cl = CntrlParameter::where(['param_cd' => 'CL', 'institution_id' => $userData->institution_id])->first();
            }
        }else{
            $cl = CntrlParameter::where(['param_cd' => 'CL', 'institution_id' => $userData->institution_id])->first();
        }
//        $cl = CntrlParameter::where(['param_cd'=>'CL', 'institution_id'=>$userData->institution_id])->first();
        $sti = CntrlParameter::where(['param_cd'=>'SL', 'institution_id'=>$userData->institution_id])->first();
        $vat = CntrlParameter::where(['param_cd'=>'TAX', 'institution_id'=>$userData->institution_id])->first();

        $cgl = str_replace('***',$branch->code, $cl->param_value);
        $cash=GlAccounts::where('acct_no', $cgl)->first();

        $sgl = str_replace('***',$branch->code, $sti->param_value);
        $stock =GlAccounts::where('acct_no', $sgl)->first();

        $vatGl = str_replace('***',$branch->code, $vat->param_value);
        $vatTax =GlAccounts::where('acct_no', $vatGl)->first();


        /**stock and goods for sales */
        $gfs =  CntrlParameter::where(['param_cd'=>'GFS', 'institution_id'=>$userData->institution_id])->first();
        $stIn =  CntrlParameter::where(['param_cd'=>'STI', 'institution_id'=>$userData->institution_id])->first();

        $gfsGl=str_replace('***',$branch->code, $gfs->param_value);
        $stInGl=str_replace('***',$branch->code, $stIn->param_value);
        $gfsGlType =GlAccounts::where('acct_no', $gfsGl)->first();
        $stInGlType = GlAccounts::where('acct_no', $stInGl)->first();

        $tran=(Array)[
            "acct_no"=>$sgl,
            "acct_type"=> $stock->acct_type ,
            "contra_acct_no"=> $cgl,
            "contra_acct_type"=>$cash->acct_type,
            "description"=> "selling products receipt no $order->receipt_no and  transaction id $order->tran_id ".date('Y-m-d H:i:s'),
            "dr_cr_ind"=>"DR/CR",
            "tran_amount"=>$request->total,
            "reversal_flag"=>'N',
            "tran_date"=>isset($request->tranDate)?$request->tranDate:now() ,
            "tran_cd"=>'STI',
            "tran_id"=> $this->generateUuid(),
            "status"=> 'Active',
            "institution_id"=> $userData->institution_id,
            "branch_id"=>$userData->branch_id ,
            "created_by"=>$userData->id,
            "created_on"=>now(),
                        // "member_id"=>null,
            // "product_id"=>$product->id,
            // "stock_id"=>null,
            "ext_ref"=>$ref,
            "tran"=>"Selling Product",
            // "reversal_reason"=>null,
            // "reversed_by"=>null,
            // "reversed_on"=>null,
        ];

        $postTran = $this->postTransaction($tran);
        $creditRequest=(object)[
            "acct_no"=>$sgl,
            "acct_type"=>$stock->acct_type,
            "tran_amt"=>$postTran->tran_amount - $totalVATPayable,
            "reversal_flag"=>'N',
            "description"=>$postTran->description,
            "transaction_date"=>$postTran->tran_date,
            "contra_acct_no"=>$cgl,
            "contra_acct_type"=>$cash->acct_type,
            "tran_type"=>'STOCK IN',
            "tran_cd"=>'STO',
            "tran_id"=>$postTran->tran_id,
            "institution_id"=>$userData->institution_id,
            "branch_id"=>$userData->branch_id,
            "created_by"=>$userData->id,
            "status"=>'Active',
        ];

        $debitRequest=(object)[
            "acct_no"=>$cgl,
            "acct_type"=>$cash->acct_type,
            "tran_amt"=>$postTran->tran_amount,
            "reversal_flag"=>'N',
            "description"=>$postTran->description,
            "transaction_date"=>$postTran->tran_date,
            "contra_acct_no"=>$sgl,
            "contra_acct_type"=>$stock->acct_type,
            "tran_type"=>'STOCK OUT',
            "tran_cd"=>'STO',
            "tran_id"=>$postTran->tran_id,
            "institution_id"=>$userData->institution_id,
            "branch_id"=>$userData->branch_id,
            "created_by"=>$userData->id,
            "status"=>'Active',
        ];

        $taxCreditRequest =(object)[
            "acct_no"=>$vatTax->acct_no,
            "acct_type"=>$vatTax->acct_type,
            "tran_amt"=> $totalVATPayable,
            "reversal_flag"=>'N',
            "description"=>$postTran->description,
            "transaction_date"=>$postTran->tran_date,
            "contra_acct_no"=>$cgl,
            "contra_acct_type"=>$cash->acct_type,
            "tran_type"=>'STOCK OUT',
            "tran_cd"=>'STO',
            "tran_id"=>$postTran->tran_id,
            "institution_id"=>$userData->institution_id,
            "branch_id"=>$userData->branch_id,
            "created_by"=>$userData->id,
            "status"=>'Active',
        ];
        $debit = $this->postGlDR($debitRequest);
        $credit = $this->postGlCR($creditRequest);



        if($request->payment_method == "CREDIT"){
            $receivable = (array)[
                "ref_no"=>$order->ref_no,
                "receipt_no"=>$order->receipt_no,
                "tran_id"=>$order->tran_id,
                "customer_id"=>$request->customer_id,
                "amount"=>  (double) $request->total,
                "amount_paid"=>0,
                "status"=>"Active",
                "institution_id"=>$userData->institution_id,
                "branch_id"=>$userData->branch_id ,
                "created_by"=>$userData->id ,
                "created_on"=> Carbon::now()
            ];
             $this->createReceivable($receivable);
        }

        if($institution->is_tax_enabled){
            $VATcredit = $this->postGlCR($taxCreditRequest);
        }
        foreach ($request->items as $value) {
            $stock = stock::where('product_id', $value['id'])->first();
            $items = new OrderItem();
            $items->order_id = $order->id;
            $items->product_id = $value['id'];
            $items->qty =  (double) $value['quantity'] * (1/$value['selected']['weight']);
            $items->selling_price =  (double) $value['price'];
            $items->status = $request->status;
            $items->institution_id = $userData->institution_id;
            $items->branch_id = $userData->institution_id;
            $items->created_by = $userData->institution_id;
            $items->measurement_unit_id = $value['selected']['unit_id'];
            $items->created_on = now();

            $stock->quantity =  (double) $stock->quantity-$value['quantity'];
            $stock->save();

            $creditStock=$debitRequest;
            $debitGoodsForSale=$creditRequest;

            $amount = $stock->purchase_price * $value['quantity'];
            // $amount = $stock->purchase_price * $value['quantity'];

            $creditStock->acct_no = $stInGl;
            $creditStock->acct_type = $stInGlType->acct_type;
            $creditStock->tran_amt = $amount;

            $debitGoodsForSale->acct_no = $gfsGl;
            $debitGoodsForSale->acct_type=$gfsGlType->acct_type;
            $debitGoodsForSale->tran_amt = $amount;

            $this->postGlDR($debitGoodsForSale);
            $this->postGlCR($creditStock);

            $items->save();
        }
        DB::commit();
        return $order;
    }

    public function getOrders(){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();

        $queryString = "SELECT O.id, O.ref_no, O.receipt_no, O.tran_id, O.item_count, O.total,
        O.discount, O.amount_paid, O.user_id, O.customer_id, O.tran_date,
        O.status, O.payment_status, O.institution_id, O.branch_id, O.created_on,
        O.created_at, O.updated_at FROM orders O";

        if($isNotAdmin){
            $queryString.=" WHERE O.institution_id = $userData->institution_id AND O.branch_id=$userData->branch_id AND O.status='Active'";
        }else{
            $queryString.=" WHERE O.status='Active'";
        }
        $queryString.=" ORDER BY O.id DESC";

        $orders = DB::select($queryString);
        return $this->genericResponse(true, "Collected successfully", 200, $orders, "getOrders", []);
    }

    public function getOderDetails($orderId){
        $queryString=" SELECT O.id, O.order_id, O.product_id,O.qty, O.status,O.institution_id, O.branch_id, O.created_at, P.name,
        P.product_no, COALESCE(O.selling_price ,S.selling_price)  AS price, I.name AS institution_name, B.name AS branch_name, Q.ref_no,
        Q.receipt_no, Q.tran_id, Q.item_count, Q.total, Q.discount, Q.amount_paid, Q.tran_date, Q.payment_status,
        CONCAT(U.first_name,' ',U.last_name,' ',U.other_name) AS user_name, B.address AS branch_address,
        I.address AS institution_address FROM order_items O
        INNER JOIN products P ON P.id = O.product_id
        INNER JOIN stocks S  ON S.product_id = P.id
        INNER JOIN institutions I ON I.id =O.institution_id
        LEFT JOIN branches B ON B.id = O.branch_id
        INNER JOIN orders Q ON Q.id = O.order_id
        INNER JOIN users U ON Q.user_id = U.id WHERE  O.order_id = $orderId";
        $orderDetails = DB::select($queryString);
        return $this->genericResponse(true, "Order details fetched successfully", 200, $orderDetails, "getOderDetails", $orderId);
    }

    public function approveBatchSales(Request $request){
        DB::beginTransaction();
        $tempArray = [];
        $total = 0;
        $stockDt = null;
        foreach ($request->sales as $sale){
            $product = Product::where(["ref_no"=>$sale["ref_no"], "institution_id"=>$sale["institution_id"] ])->first();
            $stock = stock::where(["product_id"=>$product->id, "branch_id"=>$sale["branch_id"] ])->first();
            $sum = $sale["quantity"] * $stock->selling_price;
            $total = $total + (double)$sum;
            $tempArray[] = ["id"=>$product->id, "quantity"=>$sale["quantity"], "status"=>"Active"];

            $stockDt=$sale["stock_date"];

            $updateSale = TempSale::find($sale["id"]);
            $updateSale->status="Active";
            $updateSale->save();
        }

        $newSale = (object)[
            "itemCount"=>count($tempArray),
            "total"=>$total,
            "discount"=>0,
            "amountPaid"=>$total,
            "customerId"=>null,
            "tranDate"=>$stockDt,
            "status"=>"Active",
            "Paid"=>"Paid",
            "vatEx"=>0,
            "vatInc"=>0,
            "items"=>$tempArray,
        ];
        $response = $this->newSale($newSale);
        DB::commit();
        return $this->genericResponse(true, "Approved Successfully", 200,  $response, "approveBatchSales", $request);
    }


    public function printReceipt(){

                // Set params
                $mid = '123123456';
                $store_name = 'YOURMART';
                $store_address = 'Mart Address';
                $store_phone = '1234567890';
                $store_email = 'yourmart@email.com';
                $store_website = 'yourmart.com';
                $tax_percentage = 18;
                $transaction_id = 'TX123ABC456';

                // Set items
                $items = [
                    [
                        'name' => 'French Fries (tera)',
                        'qty' => 2,
                        'price' => 65000,
                    ],
                    [
                        'name' => 'Roasted Milk Tea (large)',
                        'qty' => 1,
                        'price' => 24000,
                    ],
                    [
                        'name' => 'Honey Lime (large)',
                        'qty' => 3,
                        'price' => 10000,
                    ],
                    [
                        'name' => 'Jasmine Tea (grande)',
                        'qty' => 3,
                        'price' => 8000,
                    ],
                ];

                // Init printer
                $printer = new ReceiptPrinter;
                $printer->init(
                    config('receiptprinter.connector_type'),
                    config('receiptprinter.connector_descriptor')
                );

                // Set store info
                $printer->setStore($mid, $store_name, $store_address, $store_phone, $store_email, $store_website);

                // Add items
                foreach ($items as $item) {
                    $printer->addItem(
                        $item['name'],
                        $item['qty'],
                        $item['price']
                    );
                }
                // Set tax
                $printer->setTax($tax_percentage);

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

}
