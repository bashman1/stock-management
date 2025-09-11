<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Institution;
use App\Models\Branch;
use App\Models\TempBuildings;

class BuildingImport implements ToModel,WithHeadingRow
{
    protected $batchId;
    public function __construct(int $batchId)
    {
        $this->batchId=$batchId;
    }


    public function model(array $row)
    {

        $instData = Institution::where('ref_no', $row['institution_ref'])->first();
        $branchData=Branch::where("institution_id", $instData->id)->first();

        return new TempBuildings([
            'name'=> $row['building_name'],
            // 'building_no'=> $row['building_no'],
            // 'batch_no'=> $this->batchId,
            'street'=> $row['street'],
            'address'=> $row['address'],
            'plot'=> $row['plot_number'],
            'status'=> 'Pending',
            // 'status'=> $row['monthly_rent'],
            'created_on'=> $row['date'],
            'description'=> $row['more_info'],
            'branch_id'=>$branchData->id,
            'batch_id'=> $this->batchId,
            'institution_id'=> $instData->id,
            // 'created_on'=> now(),
        ]);

    }

}
