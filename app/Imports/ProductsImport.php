<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\Institution;
use App\Models\measurementUnit;
use App\Models\MemberBatch;
use App\Models\ProductBatche;
use App\Models\ProductCategory;
use App\Models\ProductType;
use App\Models\TempProduct;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements ToModel,WithHeadingRow
{
    protected $batchId;
    public function __construct(int $batchId)
    {
        $this->batchId=$batchId;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $instData = Institution::where('ref_no', $row['institution_ref'])->first();
        $branchData=Branch::where("institution_id", $instData->id)->first();
        $memberBatch= ProductBatche::find($this->batchId);
        $memberBatch->institution_id=$instData->id;
        $memberBatch->save();

        $productType = ProductType::where('name', $row['type'])->first();
        $productCat = ProductCategory::where('name', $row['category'])->first();
        $productUnit = measurementUnit::where('name', $row['measurement_unit'])->first();

        $type_id = null;
        if (isset($productType)){
            $type_id = $productType->id;
        }
        $cat_id = null;
        if (isset($productCat)){
            $cat_id = $productCat->id;
        }
        $unit_id = null;
        if (isset($productUnit)){
            $unit_id = $productUnit->id;
        }
        return new TempProduct([
            'name'=> $row['name'],
            'product_no'=> $row['product_no'],
            'description'=> $row['description'],
            'purchase_price'=> $row['purchase_price'],
            'selling_price'=> $row['selling_price'],
            'quantity'=> $row['quantity'],
            'min_quantity'=> $row['min_quantity'],
            'max_quantity'=> $row['max_quantity'],
            'ref_no'=> $row['institution_ref'],
            'institution_id'=>$instData->id,
            'type_id'=> $type_id,
            'batch_id'=>$memberBatch->id,
            'status'=>'Pending',
            'category_id'=> $cat_id,
            'measurement_unit_id'=> $unit_id,
            'created_on'=> now(),
        ]);
    }
}
