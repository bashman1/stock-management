<?php

namespace App\Services;

use App\Http\Controllers\Controller;
use App\Models\ComissionConfig;
use App\Models\Comission;
use Illuminate\Support\Facades\DB;

class ReportsService extends Controller {


    public function salesReport($request){

        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();
        $sqlString = "SELECT O.id, O.ref_no, O.receipt_no, O.physical_receipt_no, O.tran_id, O.item_count, O.total, O.discount,
        O.amount_paid, O.user_id, O.customer_id, O.tran_date, O.status, O.payment_status,
        O.institution_id, O.branch_id, O.created_by, O.updated_by, O.created_on, O.updated_on,
        O.created_at, O.updated_at, I.name AS institution_name, B.name AS branch_name
        FROM orders O
        INNER JOIN institutions I ON O.institution_id = I.id
        INNER JOIN branches B ON B.id = O.branch_id";
        // $sqlString .= " WHERE O.status = '$request->status'";
        // if ($isNotAdmin) {
        //     $sqlString .= " AND O.institution_id = $userData->institution_id AND O.branch_id = $userData->branch_id";
        // }

        $conditions = [];
        if (isset($request->status)){
            $conditions[] = "O.status = '" . addslashes($request->status) . "'";
        }
        if ($isNotAdmin) {
            $conditions[] = "O.institution_id = " . intval($userData->institution_id);
            $conditions[] = "O.branch_id = " . intval($userData->branch_id);
        }
        if (isset($request->from_date) && isset($request->to_date)) {
            $from_date = date('Y-m-d', strtotime($request->from_date));
            $to_date   = date('Y-m-d', strtotime($request->to_date));
            $conditions[] = "DATE(O.tran_date) BETWEEN '$from_date' AND '$to_date'";
        }else if (isset($request->from_date)) {
            $conditions[] = "DATE(O.tran_date) >= '" . addslashes($request->from_date) . "'";
        } else if (isset($request->to_date)) {
            $conditions[] = "DATE(O.tran_date) <= '" . addslashes($request->to_date) . "'";
        }
        if (isset($request->searchKey)){
            $searchKey = addslashes($request->searchKey);
            $conditions[] = "(O.ref_no LIKE '%$searchKey%' OR O.receipt_no LIKE '%$searchKey%'
            OR O.physical_receipt_no LIKE '%$searchKey%'
            OR O.tran_id LIKE '%$searchKey%' OR O.payment_status LIKE '%$searchKey%'
            OR I.name LIKE '%$searchKey%' OR B.name LIKE '%$searchKey%')";
        }
        if (count($conditions) > 0) {
            $sqlString .= " WHERE " . implode(" AND ", $conditions);
        }

        $sqlString .= " ORDER BY O.id DESC";
        $orders = DB::select($sqlString);
        return $orders;
    }
}
