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
        $product->type_id = $request->type_id ;
        $product->gauge_id = $request->gauge_id ;
        // $product->ref_no = $request-> ;
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


    public function getProducts(){
        $userData = auth()->user();
        $isNotAdmin = $this->isNotAdmin();

        $sqlString = "SELECT P.id,P.name, P.product_no, P.category_id, P.sub_category_id, P.manufacturer_id, P.supplier_id, P.measurement_unit_id, P.description,
        P.institution_id, P.user_id, P.status, P.created_by, P.updated_by, P.created_on, P.updated_on, P.created_at, P.updated_at, C.name AS category_name,
        S.name AS sub_category_name, M.name AS manufacturer, Q.name AS supplier, T.name AS unit, E.purchase_price, E.selling_price, E.discount, E.quantity,
        E.min_quantity, E.max_quantity, B.name AS branch_name, I.name AS institution_name
        FROM products P INNER JOIN product_categories C ON C.id = P.category_id
        LEFT JOIN product_sub_categories S ON P.sub_category_id = S.id
        LEFT JOIN manufacturers M ON  P.manufacturer_id = M.id
        LEFT JOIN suppliers Q ON P.supplier_id = Q.id
        LEFT JOIN measurement_units T ON P.measurement_unit_id = T.id
        INNER JOIN stocks E ON E.product_id = P.id
        INNER JOIN branches B ON B.id = E.branch_id
        INNER JOIN institutions I ON I.id =P.institution_id ";

        if ($isNotAdmin) {
            $sqlString .= " WHERE E.institution_id = $userData->institution_id AND E.branch_id = $userData->branch_id";
        }

        $sqlString .= " ORDER BY T.id DESC";

        $products = DB::select($sqlString);

        return $this->genericResponse(true, "Product list", 200, $products);
    }

}
