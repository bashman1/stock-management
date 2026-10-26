<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use Illuminate\Http\Request;
use App\Services\EfrisApiService;
use Illuminate\Support\Facades\Log;
use App\Models\EfrisComodityCategory;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{

    protected EfrisApiService $efrisService;

    public function __construct(EfrisApiService $efrisService)
    {
        $this->efrisService = $efrisService;
    }

    public function createProductCategory(Request $request)
    {
        $userData = $this->getUserLogin();
        $cat = new ProductCategory();
        $cat->name = $request->name;
        $cat->description = $request->description;
        $cat->status = $request->status;
        $cat->institution_id = $userData->institution_id;
        $cat->branch_id = $userData->branch_id;
        $cat->user_id = $userData->id;
        $cat->created_by = $userData->id;
        $cat->created_on = now();
        $cat->save();

        return $this->genericResponse(true, "Product category created Successfully", 201, $cat, "createProductCategory", $request);
    }

    public function getProductCategories()
    {

        $institutionData = $this->getUserInstitution();
        $userData = $this->getUserLogin();

        $cat = [];

        if ($institutionData->efris_enabled) {
            $cat = $this->searchEfrisCommodityCategories(new Request());
        } else {
            $cat = ProductCategory::where(["status" => "Active", "institution_id" => $userData->institution_id])->get();
        }
        return $this->genericResponse(true, "Product category fetched Successfully", 200, $cat, "getProductCategories", []);
    }

    public function createProductSubCategory(Request $request)
    {
        $userData = $this->getUserLogin();
        $subCat = new ProductSubCategory();
        $subCat->name = $request->name;
        $subCat->description = $request->description;
        $subCat->category_id = $request->category_id;
        $subCat->status = $request->status;
        $subCat->institution_id = $userData->institution_id;
        $subCat->branch_id = $userData->branch_id;
        $subCat->user_id = $userData->id;
        $subCat->created_by = $userData->id;
        $subCat->created_on = now();

        $subCat->save();

        return $this->genericResponse(true, "Product sub category created Successfully", 201, $subCat, "createProductSubCategory", $request);
    }

    public function getProductSubCategory()
    {
        $userData = $this->getUserLogin();
        $subCat = ProductSubCategory::where(["status" => "Active", "institution_id" => $userData->institution_id])->get();
        return $this->genericResponse(true, "Product sub category fetched Successfully", 200, $subCat, "getProductSubCategory", []);
    }


    public function getEfrisCommodityCategories($page)
    {
        // try {
        $institutionData = $this->getUserInstitution();
        $payload = [
            "pageNo" => $page,
            "pageSize" => "1000"
        ];

        $response = $this->efrisService->request('POST', $institutionData->tin . '/query-commodity-categories', [], $payload);
        $response = $response->json();

        // Log::info("**********************************");
        // Log::info();


        foreach ($response["data"]["records"] as $key => $value) {

            $this->createEfrisCommodityCategory($value);
            Log::info("**************** EFRIS COMMODITY CATEGORY **************** " . $page);
            Log::info($value['commodityCategoryName']);
            //save to efris_commodity_categories table
        }

        // } catch (\Throwable $th) {
        //     throw $th;
        // }
    }

    public function iteratePages()
    {

        foreach (range(153, 157) as $page) {
            $this->getEfrisCommodityCategories($page);
        }

        return $this->genericResponse(true, "Efris Commodity Categories fetched Successfully", 200, null, "iteratePages", []);
    }


    public function createEfrisCommodityCategory($value)
    {
        return DB::transaction(function () use ($value) {
            $user = $this->getUserLogin();

            return EfrisComodityCategory::firstOrCreate(
                [
                    'commodity_category_code' => $value['commodityCategoryCode']
                ],
                [
                    'commodity_category_code' => $value['commodityCategoryCode'],
                    'commodity_category_level' => $value['commodityCategoryLevel'],
                    'commodity_category_name' => $value['commodityCategoryName'],
                    'enable_status_code' => $value['enableStatusCode'],
                    'excisable' => $value['excisable'],
                    'is_leaf_node' => $value['isLeafNode'],
                    'vat_out_scope' => $value['vatOutScopeCode'],
                    'parent_code' => $value['parentCode'],
                    // 'source_now_time' => $value['nowTime'],
                    'status' => 'Active',
                    'created_by' => $user->id,
                    'created_on' => now()
                ]
            );
        });
    }

    public function searchEfrisCommodityCategories(Request $request)
    {
        $name = trim((string) $request->input('name', ''));

        $categories = EfrisComodityCategory::query()
            ->where('status', 'Active')
            ->when(
                $name !== '',
                fn($q) =>
                $q->where('commodity_category_name', 'ILIKE', "%{$name}%")
            )
            ->orderBy('commodity_category_name')
            ->limit(1000) // better than paginate when you don’t need metadata
            ->get()
            ->map(fn($c) => [
                'code'  => $c->commodity_category_code,
                'name'  => $c->commodity_category_name,
                'id'    => $c->id,
            ]);

        return $categories;
    }

}
