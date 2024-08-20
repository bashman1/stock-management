<?php

namespace App\Http\Controllers;

use App\Models\Payable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayableController extends Controller
{
    public function getPayable(Request $request)
    {
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();
        $queryString = "SELECT P.id, P.ref_no, P.tran_id, P.supplier_id, P.product_id, P.manufacturer_id, P.amount, P.amount_paid,P.status,
            P.institution_id, P.branch_id, P.created_by, P.updated_by, P.created_on, P.updated_on, P.created_at, P.updated_at,
            I.name AS institution_name, B.name AS branch_name,  CONCAT(U.first_name,' ', U.last_name,' ',U.other_name) AS user_name,
            S.name AS supplier_name, M.name AS manufacturer_name, A.name AS product_name
            FROM payables P
            INNER JOIN institutions I ON I.id = P.institution_id
            INNER JOIN branches B ON B.id =P.branch_id
            INNER JOIN users U ON U.id = P.created_by
            INNER JOIN products A ON A.id = P.product_id
            LEFT JOIN suppliers S ON S.id =P.supplier_id
            LEFT JOIN manufacturers M ON M.id =P.manufacturer_id ";

        if ($isNotAdmin) {
            $queryString .= " WHERE P.institution_id = $userData->institution_id AND P.branch_id=$userData->branch_id AND P.status='Active'";
        } else {
            $queryString .= " WHERE P.status='Active'";
        }
        $queryString .= " ORDER BY P.id DESC";
        $payable = DB::select($queryString);
        return $this->genericResponse(true, "Receivables fetched successfully", 200, $payable);
    }
}
