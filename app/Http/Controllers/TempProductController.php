<?php

namespace App\Http\Controllers;

use App\Models\ProductBatche;
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
}
