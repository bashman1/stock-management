<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\stock;
use App\Models\stockHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{

    public function createProduct(Request $request){
        $userData = auth()->user();
        $product = new Product();

        DB::beginTransaction();
        $product->name = $request->name ;
        $product->product_no = $request->product_no ;
        $product->category_id = $request->category_id ;
        $product->sub_category_id = $request->sub_category_id ;
        $product->manufacturer_id = $request->manufacturer_id ;
        $product->supplier_id = $request->supplier_id ;
        $product->measurement_unit_id = $request->measurement_unit_id ;
        $product->description = $request->description ;
        $product->institution_id = $userData->institution_id ;
        $product->user_id = $userData->id ;
        $product->status = $userData->status ;
        $product->created_by = $userData->id ;
        $product->created_on = now();
        $product->save();


        $stock = new stock();
        $history = new stockHistory();

        $stock->purchase_price = $request->purchase_price ;
        $stock->selling_price = $request->selling_price ;
        $stock->discount = $request->discount;
        $stock->product_id = $product->id;
        $stock->quantity = $request->quantity ;
        $stock->min_quantity = $request->min_quantity ;
        $stock->max_quantity = $request->max_quantity ;
        $stock->institution_id = $userData->institution_id ;
        $stock->stock_date = $request->date ;
        $stock->manufactured_date = $request->manufactured_date ;
        $stock->expiry_date = $request->expiry_date ;
        $stock->branch_id = $userData->branch_id ;
        $stock->user_id = $userData->id ;
        $stock->status = $request->status ;
        $stock->created_by = $userData->created_by ;
        $stock->created_on =now() ;
        $stock->save();


        $history->purchase_price = $request->purchase_price ;
        $history->selling_price = $request->selling_price ;
        $history->discount = $request->discount;
        $history->product_id = $product->id;
        $history->stock_id = $stock->id;
        $history->quantity = $request->quantity ;
        $history->min_quantity = $request->min_quantity ;
        $history->max_quantity = $request->max_quantity ;
        $history->institution_id = $userData->institution_id ;
        $history->stock_date = $request->date ;
        $history->manufactured_date = $request->manufactured_date ;
        $history->expiry_date = $request->expiry_date ;
        $history->branch_id = $userData->branch_id ;
        $history->user_id = $userData->id ;
        $history->status = $request->status ;
        $history->created_by = $userData->created_by ;
        $history->created_on =now() ;
        $history->save();

        DB::commit();
        return $this->genericResponse(true, "Measurement unit created successfully", 201, $product);
    }

}
