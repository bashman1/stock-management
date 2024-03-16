<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function createOrder(Request $request){
        DB::beginTransaction();
        $userData = auth()->user();
        $order = new Order();
        $ref=$this->generateTranRef();
        $order->ref_no = $ref;
        $order->receipt_no = str_replace('TRN', '', $ref) ;
        $order->tran_id = $this->generateUuid() ;
        $order->item_count = $request->itemCount ;
        $order->total = $request->total ;
        $order->discount = $request->discount ;
        $order->amount_paid = $request->amountPaid ;
        $order->user_id = $userData->id ;
        $order->customer_id = $request->customerId;
        $order->tran_date = $request->tranDate ;
        $order->status = $request->status ;
        $order->payment_status = $request->Paid ;
        $order->institution_id = $userData->institution_id;
        $order->branch_id = $userData->branch_id ;
        $order->created_by = $userData->id ;
        $order->created_on = now() ;
        $order->save();

        foreach ($request->items as $value) {
            $stock = stock::where('product_id', $value['id'])->first();
            $items = new OrderItem();
            $items->order_id = $order->id;
            $items->product_id = $value['id'];
            $items->qty = $value['quantity'];
            $items->status = $request->status;
            $items->institution_id = $userData->institution_id;
            $items->branch_id = $userData->institution_id;
            $items->created_by = $userData->institution_id;
            $items->created_on = now();

            $stock->quantity = $stock->quantity-$value['quantity'];
            $stock->save();

            $items->save();
        }
        DB::commit();
        return $this->genericResponse(true, "Order submitted successfully", 201, $order);
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
        return $this->genericResponse(true, "Collected successfully", 200, $orders);
    }

    public function getOderDetails($orderId){
        $queryString=" SELECT O.id, O.order_id, O.product_id,O.qty, O.status,O.institution_id, O.branch_id, O.created_at, P.name,
        P.product_no, S.selling_price AS price, I.name AS institution_name, B.name AS branch_name, Q.ref_no,
        Q.receipt_no, Q.tran_id, Q.item_count, Q.total, Q.discount, Q.amount_paid, Q.tran_date, Q.payment_status,
        CONCAT(U.first_name,' ',U.last_name,' ',U.other_name) AS user_name, B.address AS branch_address,
        I.address AS institution_address FROM order_items O 
        INNER JOIN products P ON P.id = O.product_id 
        INNER JOIN stocks S  ON S.product_id = P.id
        INNER JOIN institutions I ON I.id =O.institution_id
        INNER JOIN branches B ON B.id = O.branch_id
        INNER JOIN orders Q ON Q.id = O.order_id
        INNER JOIN users U ON Q.user_id = U.id WHERE  O.order_id = $orderId";
        $orderDetails = DB::select($queryString);
        return $this->genericResponse(true, "Order details fetched successfully", 200, $orderDetails);
    }
}
