<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function createInvoice(Request $request){
        try {
            $userData = $this->getUserLogin();

            $ref = $this->generateRefNumber('invoice_ref') ?? '';
            DB::beginTransaction();
            $invoice = Invoice::create([
                'name' => $request->name,
                'ref_no' => $request->ref_no,
                'invoice_no' => str_replace('INV', '', $ref) ,
                'item_count' => $request->item_count,
                'total' => $request->total,
                'discount' => $request->discount,
                'amount_paid' => $request->amountPaid,
                'user_id' => $userData->id,
                'customer_id' => $request->customer_id,
                'invoice_date' => $request->tranDate,
                'status' => $request->status,
                'address' => $request->address,
                'payment_status' => $request->payment_status ?? 'UNPAID',
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ]);


            foreach ($request->items as $item) {
                $invoiceItems = InvoiceItem::create(([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['quantity'],
                    'qty' => (double) $item['quantity'] * (1/$item['selected']['weight']),
                    // 'price' => $item['price'],
                    // 'total' => $item['total'],
                    'created_by' => $userData->id,
                    'created_on' => now(),
                    'status' => $item['status'] ?? 'Active',
                    'selling_price'=>(double) $item['price'],
                    'institution_id' => $userData->institution_id,
                    'branch_id' => $userData->branch_id,
                    'measurement_unit_id' => $item['selected']['unit_id'],
                ]));
            }

            DB::commit();
            return $this->genericResponse(true, "Invoice created successfully", 201, $invoice, "createInvoice", $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "createInvoice", $request);
        }
    }
}
