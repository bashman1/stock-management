<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\InstitutionConfig;
use App\Models\Member;
use App\Models\TempCollection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{

    public function fieldCollect(Request  $request){
        $userData = auth()->user();
        $tranIdRef= InstitutionConfig::where("type", "tran_id_ref")->first();
        $tranId = $tranIdRef->prefix.''.$tranIdRef->starting.''.$tranIdRef->current;
        $tranIdRef->current= $tranIdRef->current + $tranIdRef->step;
        $tranIdRef->save();

        $collect= new TempCollection();
        $collect->amount=$request->amount;
        $collect->description=$request->description;
        $collect->tran_date=$request->tran_date;
        $collect->member_number=$request->member_number;
        $collect->member_id=$request->member_id;
        $collect->institution_id=$userData->institution_id;
        $collect->branch_id=$userData->branch_id;
        $collect->user_id=$userData->id;
        $collect->tran_id=$tranId;
        $collect->status="Pending";
        $collect->created_by=$userData->id;
        $collect->created_on=now();
        $collect->save();

        return $this->genericResponse(true, "Collected successfully", 201, $collect);
    }

    public function getOfficerCollection(){
        $userData = auth()->user();
        $transaction = DB::select("SELECT T.*, CONCAT(M.first_name,' ',M.last_name, ' ',M.other_name) AS member_name,
            I.name AS institution_name, B.name AS branch_name,
            CONCAT(U.first_name,' ',U.last_name, ' ',U.other_name) AS officer_name
            FROM temp_collections T INNER JOIN members M ON T.member_id = M.id
            INNER JOIN institutions I ON I.id = T.institution_id
            INNER JOIN branches B ON B.id = T.branch_id
            INNER JOIN users U ON T.user_id = U.id
            WHERE T.institution_id = $userData->institution_id AND T.branch_id=$userData->branch_id AND T.user_id=$userData->branch_id");
        return $this->genericResponse(true, "Collected successfully", 201, $transaction);
    }

}
