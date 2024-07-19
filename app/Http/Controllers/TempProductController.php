<?php

namespace App\Http\Controllers;

use App\Models\ProductBatche;
use App\Models\TempMember;
use App\Models\TempProduct;
use Illuminate\Http\Request;

use App\Imports\ProductsImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class TempProductController extends Controller
{

    public function uploadProducts(Request $request){
        $batch = new ProductBatche();
        // $batch = new MemberBatch();
        $batch->name=$request->name.now();
        $batch->created_on=now();
        $batch->status='Pending';
        $batch->save();

        $path = $request->file;
        $response= Excel::import(new ProductsImport($batch->id), $path);
        return $this->genericResponse(true, "uploaded successfully", 200, $response);
    }

    /**
     * @param Request $request
     */
    public function getProductBatches(Request $request){
//        $batches=MemberBatch::all();
        // $batches=DB::select("SELECT M.*, I.name AS institution_name FROM member_batches M INNER JOIN institutions I ON I.id = M.institution_id");
        $userData = auth()->user();
        $isNotAdmin=false;
        if($userData->institution_id !=null){
            $isNotAdmin=true;
        }

        $batches = DB::table('product_batches AS M')
            ->join('institutions AS I', 'I.id', '=', 'M.institution_id')
            ->when($isNotAdmin, function ($query) use ($userData) {
                return $query->where("M.institution_id", $userData->institution_id );
            })
            ->select('M.*', 'I.name AS institution_name')
            ->get();

        return $this->genericResponse(true, "Member batches", 200, $batches);
    }

    public function getBatchProducts(Request $request){
        $members = TempProduct::where(["status"=>$request->status, "batch_id"=>$request->batchId])->get();
        return $this->genericResponse(true, "Pending members", 200, $members);
    }
}
