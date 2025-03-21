<?php

namespace App\Http\Controllers;

use App\Models\MemberBatch;
use App\Models\TempMember;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;

use App\Imports\MembersImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class TempMemberController extends Controller
{

    public function uploadMembers(Request $request){

        $batch = new MemberBatch();
        $batch->name=$request->name.now();
        $batch->created_on=now();
        $batch->status='Pending';
        $batch->save();

       $path = $request->file;
      $response= Excel::import(new MembersImport($batch->id), $path);
      return $this->genericResponse(true, "uploaded successfully", 200, $response, "uploadMembers", $request);
    }


    /**
     * @param Request $request
     */
    public function getMemberBatches(Request $request){
//        $batches=MemberBatch::all();
        // $batches=DB::select("SELECT M.*, I.name AS institution_name FROM member_batches M INNER JOIN institutions I ON I.id = M.institution_id");
        $userData = auth()->user();
        $isNotAdmin=false;
        if($userData->institution_id !=null){
            $isNotAdmin=true;
        }

    $batches = DB::table('member_batches AS M')
    ->join('institutions AS I', 'I.id', '=', 'M.institution_id')
    ->when($isNotAdmin, function ($query) use ($userData) {
        return $query->where("M.institution_id", $userData->institution_id );
    })
    ->select('M.*', 'I.name AS institution_name')
    ->get();

        return $this->genericResponse(true, "Member batches", 200, $batches, "getMemberBatches", $request);
    }


    public function getBatchMembers(Request $request){
        $members = TempMember::where(["status"=>$request->status, "batch_id"=>$request->batchId])->get();
        return $this->genericResponse(true, "Pending members", 200, $members, "getBatchMembers", $request);
    }
}
