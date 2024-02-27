<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
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
            $items = new OrderItem();
            $items->order_id = $order->id;
            $items->product_id = $value['id'];
            $items->qty = $value['quantity'];
            $items->status = $request->status;
            $items->institution_id = $userData->institution_id;
            $items->branch_id = $userData->institution_id;
            $items->created_by = $userData->institution_id;
            $items->created_on = now();
            $items->save();
        }
        DB::commit();
        return $this->genericResponse(true, "Order submitted successfully", 201, $order);
    }
}
