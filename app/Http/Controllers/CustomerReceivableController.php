<?php

namespace App\Http\Controllers;

use App\Models\CustomerReceivable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerReceivableController extends Controller
{
    public function getReceivables(Request $request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();
        $queryString = "SELECT C.id, C.receipt_no, C.tran_id, C.customer_id, C.amount, C.amount_paid, C.status, C.institution_id, C.branch_id,
        C.created_by, C.created_on, C.updated_by, C.updated_on, C.created_at, C.updated_at, A.name AS customer_name,
        I.name AS institution_name, B.name AS branch_name,  CONCAT(U.first_name,' ', U.last_name,' ',U.other_name) AS user_name
        FROM customer_receivables C
        INNER JOIN customers A ON A.id = C.customer_id
        INNER JOIN institutions I ON I.id = C.institution_id
        INNER JOIN branches B ON B.id =C.branch_id
        INNER JOIN users U ON U.id = C.created_by ";

        if ($isNotAdmin) {
            $queryString .= " WHERE C.institution_id = $userData->institution_id AND C.branch_id=$userData->branch_id AND C.status='Active'";
        } else {
            $queryString .= " WHERE C.status='Active'";
        }
        $queryString.=" ORDER BY C.id DESC";
        $receivables = DB::select($queryString);
        return $this->genericResponse(true, "Receivables fetched successfully", 200, $receivables, "getReceivables", []);
    }


    public function makePayment(Request $request){
        try {
            $receivable = CustomerReceivable::find($request->receivable_id);
            if (!$receivable) {
                return $this->genericResponse(false, "Receivable not found", 404, null, "makePayment", $request);
            }

            $receivable->amount_paid += $request->amount_paid;

            if ($receivable->amount_paid >= $receivable->amount) {
                $receivable->status = 'Paid';
            }
            // else {
            //     $receivable->status = 'Partially Paid';
            // }
            $receivable->updated_by = $this->getUserLogin()->id;
            $receivable->updated_on = now();
            $receivable->save();
            return $this->genericResponse(true, "Payment made successfully", 200, $receivable, "makePayment", $request);

        } catch (\Throwable $th) {
            return $this->genericResponse(false, "An error occurred while making payment: " . $th->getMessage(), 500, null, "makePayment", $request);
        }
    }
}
