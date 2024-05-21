<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommonController extends Controller
{

    public function getDashboardStats(){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();
        $data = null;
        $collectionGraph=null;
        $membersGraph =null;
        $productsGraph =null;
        $salesGraph =null;
        $bestSellingProduct=[];

        if($isNotAdmin){
            $inventoryAcct= $this->getControlAcctByCode("STI");
            $salesAcct= $this->getControlAcctByCode("SL");
            $data = DB::select("SELECT
            -- (SELECT COUNT(id) FROM institutions WHERE status ='Active' AND institution_id = $userData->institution_id AND branch_id=$userData->branch_id) AS total_institutions,
            (SELECT COUNT(id) FROM branches WHERE status ='Active' AND institution_id = $userData->institution_id) AS total_branches,
            (SELECT COUNT(id) FROM users WHERE status ='Active' AND institution_id = $userData->institution_id AND branch_id=$userData->branch_id) AS total_users,
            (SELECT COUNT(id) FROM members WHERE status ='Active' AND institution_id = $userData->institution_id AND branch_id=$userData->branch_id) AS total_customers,
            (SELECT sum(amount) FROM temp_collections WHERE status ='Pending' AND institution_id = $userData->institution_id AND branch_id=$userData->branch_id) AS total_pending_collection,
            (SELECT sum(amount) FROM temp_collections WHERE status ='Declined' AND institution_id = $userData->institution_id AND branch_id=$userData->branch_id) AS total_declined_collection,
            (SELECT sum(amount) FROM collections WHERE status ='Active' AND institution_id = $userData->institution_id AND branch_id=$userData->branch_id) AS total_approved_collection,
            (SELECT COUNT(id) FROM products WHERE status ='Active' AND institution_id = $userData->institution_id) AS products,
            (SELECT COUNT(id) FROM orders WHERE status ='Active' AND institution_id = $userData->institution_id AND branch_id=$userData->branch_id) AS sales,
            (SELECT COUNT(id) FROM order_items WHERE status ='Active' AND institution_id = $userData->institution_id AND branch_id=$userData->branch_id) AS product_sold,
            (SELECT SUM(tran_amount) FROM gl_histories WHERE acct_no = '$inventoryAcct' AND reversal_flag = 'N') AS total_stock_value,
            (SELECT SUM(tran_amount) FROM gl_histories WHERE acct_no = '$inventoryAcct' AND reversal_flag = 'N' AND DATE(transaction_date) = CURRENT_DATE) AS today_stock_value,
            (SELECT SUM(tran_amount) FROM gl_histories WHERE acct_no = '$salesAcct' AND reversal_flag = 'N') AS total_sales_value,
            (SELECT SUM(tran_amount) FROM gl_histories WHERE acct_no = '$salesAcct' AND reversal_flag = 'N' AND DATE(transaction_date) = CURRENT_DATE) AS today_sales_value,
            (SELECT COALESCE(SUM(S.selling_price) - SUM(S.purchase_price), 0)  FROM orders O INNER JOIN order_items I ON O.id= I.order_id INNER JOIN stocks S ON I.product_id = S.product_id
            WHERE DATE ( tran_date ) = CURRENT_DATE AND O.institution_id = $userData->institution_id AND O.branch_id=$userData->branch_id ) AS today_mark_up,
            (SELECT COALESCE(ROUND(CAST(((SUM(S.selling_price) - SUM(S.purchase_price))/SUM(S.purchase_price))*100 AS NUMERIC), 2), 0)  FROM orders O INNER JOIN order_items I ON O.id= I.order_id
            INNER JOIN stocks S ON I.product_id = S.product_id WHERE DATE(tran_date) = CURRENT_DATE AND O.institution_id = $userData->institution_id AND O.branch_id=$userData->branch_id ) AS today_mark_up_percentage,
            (SELECT COALESCE(SUM(H.tran_amount), 0) FROM gl_histories H INNER JOIN gl_accounts A ON H.acct_no = A.acct_no WHERE A.acct_type = 'EXPENSE' AND H.institution_id = $userData->institution_id AND H.branch_id=$userData->branch_id) AS total_expenses,
            (SELECT COALESCE(SUM(H.tran_amount), 0) FROM gl_histories H INNER JOIN gl_accounts A ON H.acct_no = A.acct_no WHERE A.acct_type = 'EXPENSE' AND H.institution_id = $userData->institution_id AND H.branch_id=$userData->branch_id AND DATE(transaction_date) = CURRENT_DATE) AS today_expenses
            ");

            $collectionGraph=DB::select("SELECT COALESCE(COUNT(c.id), 0) AS count, date_trunc('MONTH', gs.month) AS month FROM
            generate_series((NOW() - INTERVAL '12 MONTH')::date, NOW()::date, '1 month'::interval)
            AS gs(month) LEFT JOIN collections c ON date_trunc('MONTH', c.created_at) = date_trunc('MONTH', gs.month)
            AND c.institution_id = $userData->institution_id AND c.branch_id=$userData->branch_id
            GROUP BY date_trunc('MONTH', gs.month) ORDER BY date_trunc('MONTH', gs.month)");

            $membersGraph =DB::select("SELECT COALESCE(COUNT(m.id), 0) AS count, date_trunc('MONTH', gs.month) AS month FROM
            generate_series((NOW() - INTERVAL '12 MONTH')::date, NOW()::date, '1 month'::interval)
            AS gs(month) LEFT JOIN members m ON date_trunc('MONTH', m.created_at) = date_trunc('MONTH', gs.month)
            AND m.institution_id = $userData->institution_id AND m.branch_id=$userData->branch_id
            GROUP BY date_trunc('MONTH', gs.month) ORDER BY date_trunc('MONTH', gs.month) ");

            $productsGraph =DB::select("SELECT COALESCE(COUNT(c.id), 0) AS count, date_trunc('MONTH', gs.month) AS month FROM
            generate_series((NOW() - INTERVAL '12 MONTH')::date, NOW()::date, '1 month'::interval)
            AS gs(month) LEFT JOIN products c ON date_trunc('MONTH', c.created_at) = date_trunc('MONTH', gs.month)
            AND c.institution_id = $userData->institution_id
            GROUP BY date_trunc('MONTH', gs.month) ORDER BY date_trunc('MONTH', gs.month)");

            $salesGraph =DB::select("SELECT COALESCE(COUNT(c.id), 0) AS count, date_trunc('MONTH', gs.month) AS month FROM
            generate_series((NOW() - INTERVAL '12 MONTH')::date, NOW()::date, '1 month'::interval)
            AS gs(month) LEFT JOIN orders c ON date_trunc('MONTH', c.created_at) = date_trunc('MONTH', gs.month)
            AND c.institution_id = $userData->institution_id AND c.branch_id=$userData->branch_id
            GROUP BY date_trunc('MONTH', gs.month) ORDER BY date_trunc('MONTH', gs.month)");

            $bestSellingProduct=DB::select("SELECT P.name, S.purchase_price, S.selling_price, SUM(I.qty) AS quantity
            FROM orders O INNER JOIN order_items I ON O.id = I.order_id
            INNER JOIN products P ON P.id = I.product_id
            INNER JOIN stocks S ON P.id = S.product_id
            WHERE O.institution_id = $userData->institution_id AND O.branch_id=$userData->branch_id    --DATE(O.tran_date) = CURRENT_DATE
            GROUP BY P.name, S.purchase_price, S.selling_price
            ORDER BY quantity DESC
            LIMIT 10;");

        }else{
            $data = DB::select("SELECT
            (SELECT COUNT(id) FROM institutions WHERE status ='Active') AS total_institutions,
            (SELECT COUNT(id) FROM branches WHERE status ='Active') AS total_branches,
            (SELECT COUNT(id) FROM users WHERE status ='Active') AS total_users,
            (SELECT COUNT(id) FROM members WHERE status ='Active') AS total_customers,
            (SELECT sum(amount) FROM temp_collections WHERE status ='Pending') AS total_pending_collection,
            (SELECT sum(amount) FROM temp_collections WHERE status ='Declined') AS total_declined_collection,
            (SELECT sum(amount) FROM collections WHERE status ='Active') AS total_approved_collection,
            (SELECT COUNT(id) FROM products WHERE status ='Active') AS products,
            (SELECT COUNT(id) FROM orders WHERE status ='Active') AS sales,
            (SELECT COUNT(id) FROM order_items WHERE status ='Active') AS product_sold
            -- (SELECT SUM(tran_amount) FROM gl_histories WHERE  reversal_flag = 'N') AS total_stock_value,
            -- (SELECT SUM(tran_amount) FROM gl_histories WHERE  reversal_flag = 'N') AS today_stock_value
            -- (SELECT SUM(tran_amount) FROM gl_histories WHERE  AND reversal_flag = 'N') AS total_sales_value
            -- (SELECT SUM(tran_amount) FROM gl_histories WHERE  AND reversal_flag = 'N') AS today_sales_value
            ");
            $collectionGraph=DB::select("SELECT COALESCE(COUNT(c.id), 0) AS count, date_trunc('MONTH', gs.month) AS month FROM
            generate_series((NOW() - INTERVAL '12 MONTH')::date, NOW()::date, '1 month'::interval)
            AS gs(month) LEFT JOIN collections c ON date_trunc('MONTH', c.created_at) = date_trunc('MONTH', gs.month)
            GROUP BY date_trunc('MONTH', gs.month) ORDER BY date_trunc('MONTH', gs.month)");

            $membersGraph =DB::select("SELECT COALESCE(COUNT(m.id), 0) AS count, date_trunc('MONTH', gs.month) AS month FROM
            generate_series((NOW() - INTERVAL '12 MONTH')::date, NOW()::date, '1 month'::interval)
            AS gs(month) LEFT JOIN members m ON date_trunc('MONTH', m.created_at) = date_trunc('MONTH', gs.month)
            GROUP BY date_trunc('MONTH', gs.month) ORDER BY date_trunc('MONTH', gs.month) ");

            $productsGraph =DB::select("SELECT COALESCE(COUNT(c.id), 0) AS count, date_trunc('MONTH', gs.month) AS month FROM
            generate_series((NOW() - INTERVAL '12 MONTH')::date, NOW()::date, '1 month'::interval)
            AS gs(month) LEFT JOIN products c ON date_trunc('MONTH', c.created_at) = date_trunc('MONTH', gs.month)
            GROUP BY date_trunc('MONTH', gs.month) ORDER BY date_trunc('MONTH', gs.month)");

            $salesGraph =DB::select("SELECT COALESCE(COUNT(c.id), 0) AS count, date_trunc('MONTH', gs.month) AS month FROM
            generate_series((NOW() - INTERVAL '12 MONTH')::date, NOW()::date, '1 month'::interval)
            AS gs(month) LEFT JOIN orders c ON date_trunc('MONTH', c.created_at) = date_trunc('MONTH', gs.month)
            GROUP BY date_trunc('MONTH', gs.month) ORDER BY date_trunc('MONTH', gs.month)");
        }

        return $this->genericResponse(true, "Stats", 200, ["count"=>$data, "collectionGraph"=>$collectionGraph, "membersGraph"=>$membersGraph, "productsGraph"=>$productsGraph, "salesGraph"=>$salesGraph, "bestSellingProduct"=>$bestSellingProduct]);
    }
}
