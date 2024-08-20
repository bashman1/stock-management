<?php

namespace App\Http\Controllers;

use App\Models\CustomerReceivable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerReceivableController extends Controller
{
    public function getReceivables(Request $request)
    {
        $userData = auth()->user();
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
        return $this->genericResponse(true, "Receivables fetched successfully", 200, $receivables);
    }
}
