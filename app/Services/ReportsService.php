<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\ComissionConfig;
use App\Models\Comission;
use Illuminate\Support\Facades\DB;

class ReportsService extends Controller {

 
    public function salesReport($request){

        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();
        $sqlString = "SELECT O.id, O.ref_no, O.receipt_no, O.tran_id, O.item_count, O.total, O.discount,
        O.amount_paid, O.user_id, O.customer_id, O.tran_date, O.status, O.payment_status,
        O.institution_id, O.branch_id, O.created_by, O.updated_by, O.created_on, O.updated_on,
        O.created_at, O.updated_at, I.name AS institution_name, B.name AS branch_name
        FROM orders O
        INNER JOIN institutions I ON O.institution_id = I.id
        INNER JOIN branches B ON B.id = O.branch_id";
        $sqlString .= " WHERE O.status = '$request->status'";
        if ($isNotAdmin) {
            $sqlString .= " AND O.institution_id = $userData->institution_id AND O.branch_id = $userData->branch_id";
        }
        $sqlString .= " ORDER BY O.id DESC";
        $orders = DB::select($sqlString);
        return $orders;
    }
}
