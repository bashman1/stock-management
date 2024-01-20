<?php

namespace App\Http\Controllers;

use App\Models\Comission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ComissionController extends Controller
{

    public function getCommissionsEarned(){

        $query = "SELECT C.id, C.tran_id, C.amount AS commision, C.commission_config_id, C.institution_id, C.branch_id, C.user_id, C.status,
        C.created_on, C.updated_on, C.updated_by, C.created_at, C.updated_at, T.amount, T.description, T.tran_date, T.member_number,
        CONCAT(M.first_name, M.last_name, M.other_name) AS member_name, M.phone_number, M.alt_member_number, G.name AS commission_name,
        CONCAT(G.amount, ' ', G.commission_type) AS rate, I.name AS institution_name, B.name AS branch_name,
        CONCAT(U.first_name, ' ', U.last_name,' ', U.other_name) AS officer_name
        FROM comissions C
        INNER JOIN collections T ON C.tran_id = T.tran_id
        INNER JOIN members M ON M.member_number = T.member_number
        INNER JOIN comission_configs G ON G.id = C.commission_config_id
        INNER JOIN institutions I ON I.id = C.institution_id
        INNER JOIN branches B ON B.id =  C.branch_id
        INNER JOIN users U ON U.id=C.user_id";

        $commissions= DB::select($query);
        return $this->genericResponse(true, "commissions", 200, $commissions);
    }
}
