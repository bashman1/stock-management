<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{

    public function createProductCategory(Request $request){
        $userData = auth()->user();
        $cat = new ProductCategory();
        $cat->name = $request->name;
        $cat->description = $request->description;
        $cat->status = $request->status;
        $cat->institution_id = $userData->institution_id;
        $cat->branch_id = $userData->branch_id;
        $cat->user_id = $userData->id;
        $cat->created_by = $userData->id;
        $cat->created_on=now();
        $cat->save();

        return $this->genericResponse(true, "Product category created Successfully", 201, $cat, "createProductCategory", $request);
    }

    public function getProductCategories(){
        $userData = auth()->user();
        $cat = ProductCategory::where(["status"=>"Active", "institution_id"=>$userData->institution_id])->get();
        return $this->genericResponse(true, "Product category fetched Successfully", 200, $cat, "getProductCategories", []);
    }

    public function createProductSubCategory(Request $request){
        $userData = auth()->user();
        $subCat = new ProductSubCategory();
        $subCat->name = $request->name ;
        $subCat->description = $request->description ;
        $subCat->category_id = $request->category_id ;
        $subCat->status = $request->status;
        $subCat->institution_id = $userData->institution_id ;
        $subCat->branch_id = $userData->branch_id ;
        $subCat->user_id = $userData->id ;
        $subCat->created_by = $userData->id ;
        $subCat->created_on = now();

        $subCat->save();

        return $this->genericResponse(true, "Product sub category created Successfully", 201, $subCat, "createProductSubCategory", $request);
    }

    public function getProductSubCategory(){
        $userData = auth()->user();
        $subCat = ProductSubCategory::where(["status"=>"Active", "institution_id"=>$userData->institution_id])->get();
        return $this->genericResponse(true, "Product sub category fetched Successfully", 200, $subCat, "getProductSubCategory", []);
    }
}
