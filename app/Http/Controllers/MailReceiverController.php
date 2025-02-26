<?php

namespace App\Http\Controllers;

use App\Imports\MailReceiverImport;
use App\Models\MailReceiver;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class MailReceiverController extends Controller
{
    public function uploadMailReceivers(Request $request){
        $path = $request->file;
        $response = Excel::import(new MailReceiverImport(), $path);
        return $this->genericResponse(true, "Mail receiver uploaded successfully", 201, $response, "uploadMailReceivers", $request);
    }


    public function getMailReceivers(){
        try {
            $userData = auth()->user();
            $isNotAdmin = $this->isNotAdmin();
             $queryString = "SELECT M.id, M.name, M.email, M.category, M.user_id, M.institution_id, M.branch_id,
            M.created_by, M.updated_by, M.created_on, M.updated_on, M.created_at, M.updated_at,
            CONCAT(U.first_name,' ',U.last_name,' ',U.other_name) AS user_name,
            I.name AS institution_name, B.name AS branch_name
            FROM mail_receivers M
            INNER JOIN users U ON U.id = M.user_id
            LEFT JOIN institutions I ON I.id = M.institution_id
            LEFT JOIN branches B ON B.id = M.branch_id " ;

            if ($isNotAdmin) {
                $queryString .= " WHERE M.institution_id = $userData->institution_id  ";
            }
            $queryString .= " ORDER BY M.id DESC ";
            $mailReceivers = DB::select($queryString);
            return $this->genericResponse(true, "Ledger account created successfully", 200, $mailReceivers, "getMailReceivers", []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, null, "getMailReceivers", []);
        }
    }
}
