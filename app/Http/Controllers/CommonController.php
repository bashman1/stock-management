<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommonController extends Controller
{

    public function getDashboardStats(){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();
        // $queryString="";
        // if($isNotAdmin){
        //     $queryString.=" WHERE T.institution_id = $userData->institution_id AND T.branch_id=$userData->branch_id ";
        // }

        $data = DB::select("SELECT
        (SELECT COUNT(id) FROM institutions WHERE status ='Active') AS total_institutions,
        (SELECT COUNT(id) FROM branches WHERE status ='Active') AS total_branches,
        (SELECT COUNT(id) FROM users WHERE status ='Active') AS total_users,
        (SELECT COUNT(id) FROM members WHERE status ='Active') AS total_customers,
        (SELECT sum(amount) FROM temp_collections WHERE status ='Pending') AS total_pending_collection,
        (SELECT sum(amount) FROM temp_collections WHERE status ='Declined') AS total_declined_collection,
        (SELECT sum(amount) FROM collections WHERE status ='Active') AS total_approved_collection");

        $collectionGraph=DB::select("SELECT COALESCE(COUNT(c.id), 0) AS count, date_trunc('MONTH', gs.month) AS month FROM
        generate_series((NOW() - INTERVAL '12 MONTH')::date, NOW()::date, '1 month'::interval)
        AS gs(month) LEFT JOIN collections c ON date_trunc('MONTH', c.created_at) = date_trunc('MONTH', gs.month)
        GROUP BY date_trunc('MONTH', gs.month) ORDER BY date_trunc('MONTH', gs.month)");

        $membersGraph =DB::select("SELECT COALESCE(COUNT(m.id), 0) AS count, date_trunc('MONTH', gs.month) AS month FROM
        generate_series((NOW() - INTERVAL '12 MONTH')::date, NOW()::date, '1 month'::interval)
        AS gs(month) LEFT JOIN members m ON date_trunc('MONTH', m.created_at) = date_trunc('MONTH', gs.month)
        GROUP BY date_trunc('MONTH', gs.month) ORDER BY date_trunc('MONTH', gs.month) ");

        return $this->genericResponse(true, "Stats", 200, ["count"=>$data, "collectionGraph"=>$collectionGraph, "membersGraph"=>$membersGraph]);
    }
}
