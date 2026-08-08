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
                'ref_no' => $ref,
                'invoice_no' => str_replace('INV', '', $ref) ,
                'item_count' => count($request->items),
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
                // selected.weight is the conversion_factor (base units per chosen unit).
                // qty stays in the chosen unit; base_qty is the base-unit equivalent.
                $conversion = (double) $item['selected']['weight'];
                $invoiceItems = InvoiceItem::create(([
                    'invoice_id' => $invoice->id,
                    'product_id' => $item['id'],
                    'qty' => (double) $item['quantity'],
                    'base_qty' => (double) $item['quantity'] * $conversion,
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


    public function getInvoices(Request $request){
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();

            $sqlString= "SELECT I.id, I.name, I.ref_no, I.invoice_no, I.invoice_type,
                I.item_count, I.total, I.amount_paid, I.payment_status, I.address,
                I.institution_id, I.branch_id, I.created_by, I.created_at, I.customer_id,
                C.name AS customer_name, Q.name AS institution_name,
                CONCAT(U.first_name,' ', U.last_name,' ',U.other_name) AS user_name,
                B.name AS branch_name
                FROM invoices I
                LEFT JOIN customers C ON C.id = I.customer_id
                INNER JOIN institutions Q ON Q.id = I.institution_id
                INNER JOIN users U ON U.id = I.user_id
                INNER JOIN branches B ON B.id = I.branch_id
                WHERE I.status = 'Active' ";

            $totalSql = "SELECT COUNT(I.id) AS total
                FROM invoices I
                LEFT JOIN customers C ON C.id = I.customer_id
                INNER JOIN institutions Q ON Q.id = I.institution_id
                INNER JOIN users U ON U.id = I.user_id
                INNER JOIN branches B ON B.id = I.branch_id
                WHERE I.status = 'Active' ";

            if ($isNotAdmin) {
                $sqlString .= " AND I.institution_id = {$userData->institution_id} AND I.branch_id = {$userData->branch_id}";
                $totalSql .= " AND I.institution_id = {$userData->institution_id} AND I.branch_id = {$userData->branch_id}";
            }

            if ($request->search) {
                $search = $request->search;
                $sqlString .= " AND (I.name ILIKE '%{$search}%' OR C.name ILIKE '%{$search}%')";
                $totalSql .= " AND (I.name ILIKE '%{$search}%' OR C.name ILIKE '%{$search}%')";
            }

            if (!empty(trim($request->startDate))) {
                $startDate = date('Y-m-d', strtotime(trim($request->startDate))); // only date part
                $sqlString .= " AND I.created_at::date >= '$startDate'";
                $totalSql .= " AND I.created_at::date >= '$startDate'";
            }
            if (!empty(trim($request->endDate))) {
                $endDate = date('Y-m-d', strtotime(trim($request->endDate))); // only date part
                $sqlString .= " AND I.created_at::date <= '$endDate'";
                $totalSql .= " AND I.created_at::date <= '$endDate'";
            }

            $sqlString .= " ORDER BY I.id DESC";

            $invoices = $this->paginate($request->rowPerPage, $totalSql, $sqlString);

            return $this->genericResponse(true, "Invoices retrieved successfully", 200, $invoices, "getInvoices", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getInvoices", $request);
        }
    }
}
