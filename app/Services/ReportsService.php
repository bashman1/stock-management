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
        O.amount_paid, O.payment_method, O.user_id, O.customer_id, O.tran_date, O.status, O.payment_status,
        O.institution_id, O.branch_id, O.created_by, O.updated_by, O.created_on, O.updated_on,
        O.created_at, O.updated_at, I.name AS institution_name, B.name AS branch_name
        FROM orders O
        INNER JOIN institutions I ON O.institution_id = I.id
        INNER JOIN branches B ON B.id = O.branch_id";
        // $sqlString .= " WHERE O.status = '$request->status'";
        // if ($isNotAdmin) {
        //     $sqlString .= " AND O.institution_id = $userData->institution_id AND O.branch_id = $userData->branch_id";
        // }

        // User input is bound, not interpolated: addslashes is not a valid escape for PostgreSQL.
        $bindings = [];
        $conditions = [];
        if (isset($request->status)){
            $conditions[] = "O.status = ?";
            $bindings[] = $request->status;
        }
        if ($isNotAdmin) {
            $conditions[] = "O.institution_id = " . intval($userData->institution_id);
            $conditions[] = "O.branch_id = " . intval($userData->branch_id);
        }
        $fromDate = $this->safeDate($request->from_date);
        $toDate = $this->safeDate($request->to_date);
        if ($fromDate) {
            $conditions[] = "DATE(O.tran_date) >= ?";
            $bindings[] = $fromDate;
        }
        if ($toDate) {
            $conditions[] = "DATE(O.tran_date) <= ?";
            $bindings[] = $toDate;
        }
        if (!empty(trim((string) $request->searchKey))){
            $like = '%' . trim($request->searchKey) . '%';
            $conditions[] = "(O.ref_no ILIKE ? OR O.receipt_no ILIKE ? OR O.physical_receipt_no ILIKE ?
            OR O.tran_id ILIKE ? OR O.payment_status ILIKE ? OR I.name ILIKE ? OR B.name ILIKE ?)";
            array_push($bindings, $like, $like, $like, $like, $like, $like, $like);
        }
        if (count($conditions) > 0) {
            $sqlString .= " WHERE " . implode(" AND ", $conditions);
        }

        $sqlString .= " ORDER BY O.id DESC";
        $orders = DB::select($sqlString, $bindings);
        return $orders;
    }
}
