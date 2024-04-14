<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{

    public function getInventoryReport(Request $request){

        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();
        $sqlString =  "SELECT P.id, P.name, P.product_no, P.category_id, P.sub_category_id, P.manufacturer_id,
        P.supplier_id, P.measurement_unit_id, P.description, P.institution_id, P.user_id,P.status,
        P.created_by, P.updated_by, P.created_on, P.updated_on, P.created_at, P.updated_at,
        P.type_id, P.gauge_id, C.name AS category_name, S.name AS sub_category_name,
        M.name AS manufacturer_name, M.website, M.email,M.phone_number,M.country_id,
        M.description AS manufacturer_description, R.name AS manufacture_country,
        K.name AS measurement_unit_name,I.name AS institution_name,
        T.name AS supplier_name, CONCAT(U.first_name, ' ', U.last_name,' ', U.other_name) AS user_name,
        Q.name AS type_name, G.name AS gauge_name, Y.purchase_price, Y.selling_price,
        Y.quantity, Y.min_quantity, Y.max_quantity, Y.stock_date,Y.manufactured_date,
        Y.expiry_date, B.name AS branch_name FROM products P
        LEFT JOIN product_categories C ON C.id = P.category_id
        LEFT JOIN product_sub_categories S ON S.id=P.sub_category_id
        LEFT JOIN manufacturers M ON M.id =P.measurement_unit_id
        LEFT JOIN country_refs R ON M.country_id =R.id
        LEFT JOIN suppliers T ON T.id = P.supplier_id
        INNER JOIN measurement_units K ON K.id = P.measurement_unit_id
        INNER JOIN institutions I ON I.id = P.institution_id
        INNER JOIN users U ON U.id = P.user_id
        LEFT JOIN product_types Q ON Q.id = P.type_id
        LEFT JOIN product_gauges G ON G.id = P.gauge_id
        INNER JOIN stocks Y ON P.id = Y.product_id
        INNER JOIN branches B ON Y.branch_id = B.id";
        $sqlString .= " WHERE P.status = '$request->status'";
        if ($isNotAdmin) {
            $sqlString .= " AND P.institution_id = $userData->institution_id AND Y.branch_id = $userData->branch_id";
        }
        $sqlString .= " ORDER BY P.id DESC";
        $products = DB::select($sqlString);
        return $this->genericResponse(true, "Product list", 200, $products);
    }

    /**
     * Undocumented function
     *
     * @param Request $request
     * @return void
     */
    public function getSalesReport(Request $request){
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

        return $this->genericResponse(true, "Product list", 200, $orders);
    }
}
