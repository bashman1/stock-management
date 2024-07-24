<?php

namespace App\Http\Controllers;

use App\Imports\SalesImport;
use App\Models\SalesBatch;
use App\Models\TempProduct;
use App\Models\TempSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use DateTime;


class TempSaleController extends Controller
{
    public function uploadSales(Request $request){



        $batch = new SalesBatch();
        $batch->name=$request->name.now();
        $batch->stock_date=$request->date;
        $batch->created_on=now();
        $batch->status='Pending';
        $batch->save();

        $path = $request->file;
        $response= Excel::import(new SalesImport($batch->id), $path);
        return $this->genericResponse(true, "uploaded successfully", 200, $response);
    }


    /**
     * @param Request $request
     */
    public function getSalesBatches(Request $request){
//        $batches=MemberBatch::all();
        // $batches=DB::select("SELECT M.*, I.name AS institution_name FROM member_batches M INNER JOIN institutions I ON I.id = M.institution_id");
        $userData = auth()->user();
        $isNotAdmin=false;
        if($userData->institution_id !=null){
            $isNotAdmin=true;
        }

        $batches = DB::table('sales_batches AS M')
            ->join('institutions AS I', 'I.id', '=', 'M.institution_id')
            ->when($isNotAdmin, function ($query) use ($userData) {
                return $query->where("M.institution_id", $userData->institution_id );
            })
            ->select('M.*', 'I.name AS institution_name')
            ->get();

        return $this->genericResponse(true, "Member batches", 200, $batches);
    }

    public function getBatchProducts(Request $request){

        $sales = DB::table('temp_sales as T')
            ->join('products as P', 'P.ref_no', '=', 'T.ref_no')
            ->select('P.name as product_name', 'T.*')
            ->where(["T.status"=>$request->status, "T.batch_id"=>$request->batchId])
            ->get();
//        $members = TempProduct::where(["status"=>$request->status, "batch_id"=>$request->batchId])->get();
        return $this->genericResponse(true, "Pending members", 200, $sales);
    }

}
