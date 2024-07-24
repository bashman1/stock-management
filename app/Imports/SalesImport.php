<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\Institution;
use App\Models\SalesBatch;
use App\Models\TempSale;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SalesImport implements ToModel,WithHeadingRow
{

    protected $batchId;
    protected $stockDate;
    public function __construct(int $batchId)
    {
        $this->batchId=$batchId;
//        $this->stockDate=$stockDate;
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
        $salesBatch = SalesBatch::find($this->batchId);
        $salesBatch->institution_id=$instData->id;
        $salesBatch->save();

        return new TempSale([
            'ref_no' => $row['product_ref_no'],
            'status' => 'Pending',
            'stock_date' => $salesBatch->stock_date,
            'quantity' => $row['quantity'],
            'institution_id' => $instData->id,
            'batch_id'=>$this->batchId,
            'branch_id' => $branchData->id,
            'created_on'=> now(),
        ]);
    }
}
