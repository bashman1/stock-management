<?php
namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Institution;
use App\Models\Branch;
use App\Models\Building;
use App\Models\TempBuildingUnit;

class BuildingUnitImport implements ToModel,WithHeadingRow{


    protected $batchId;
    public function __construct(int $batchId)
    {
        $this->batchId=$batchId;
    }

    public function model(array $row)
    {

        $instData = Institution::where('ref_no', $row['institution_ref'])->first();
        $branchData=Branch::where(["institution_id"=> $instData->id, "code"=> $row['branch_ref']])->first();

        $building = Building::where(['ref_no'=> $row['building_ref'], 'institution_id'=> $instData->id])->first();
        if(!$building){
            throw new \Exception("Building with reference ".$row['building_ref']." not found for institution ".$instData->name);
        }

        return new TempBuildingUnit([
            'name'=> $row['unit_name'],
            'building_id'=> $building->id,
            'unit_type'=> 'Room',
            'status'=> 'Pending',
            'unit_no'=> $row['unit_no'],
            'rent'=> $row['monthly_rent'],
            // 'status'=> $row['status'] ?? 'Available',
            // 'description'=> $row['more_info'],
            'batch_id'=> $this->batchId,
            'branch_id'=>$branchData->id,
            'institution_id'=> $instData->id,
            // 'created_on'=> now(),
        ]);

    }

}
