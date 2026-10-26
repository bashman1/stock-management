<?php

namespace App\Imports;

use App\Models\TempRental;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Institution;
use App\Models\Branch;
use App\Models\Building;
use App\Models\BuildingUnit;
use App\Models\Member;

class RentalImport implements ToModel, WithHeadingRow
{
    private $batch_id;

    public function __construct($batch_id)
    {
        $this->batch_id = $batch_id;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        $instData = Institution::where('ref_no', $row['institution_ref'])->first();
        $branchData = Branch::where(["institution_id" => $instData->id, "code" => $row['branch_ref']])->first();

        $building = Building::where(['ref_no' => $row['building_ref'], 'institution_id' => $instData->id])->first();
        if (!$building) {
            throw new \Exception("Building with reference " . $row['building_ref'] . " not found for institution " . $instData->name);
        }

        $unit = BuildingUnit::where(['ref_no' => $row['unit_ref'], 'building_id' => $building->id])->first();
        if (!$unit) {
            throw new \Exception("Building Unit with reference " . $row['unit_ref'] . " not found for building " . $building->name);
        }

        $member = Member::where(['member_number' => $row['tenant_ref'], 'institution_id' => $instData->id])->first();
        if (!$member) {
            throw new \Exception("Member with reference " . $row['member_ref'] . " not found for institution " . $instData->name);
        }

        return new TempRental([
            'building_id' => $building->id,
            'unit_id' => $unit->id,
            'member_id' => $member->id,
            'duration' => (float) $row['duration'],
            'amount_paid' => (float) $row['amount_paid'],
            'status' => 'Pending',
            // 'rental_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['start_date'])->format('Y-m-d'),
            'rental_date' => $this->parseExcelDate($row['start_date']),
            // 'ref_no' => $row['ref_no'],
            // 'due_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['due_date'])->format('Y-m-d'),
            // 'paid_date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['paid_date'])->format('Y-m-d'),
            // 'description' => $row['description'],
            'institution_id' => $instData->id,
            'branch_id' => $branchData->id,
            'batch_id' => $this->batch_id,
            // 'created_by' => auth()->id(),
            // 'created_on' => now(),
            // 'payment_status' => $row['payment_status'] ?? 'Unpaid',
        ]);
    }


    private function parseExcelDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // Case 1: Numeric Excel serial number
        if (is_numeric($value)) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
        }

        // Case 2: Already a string date like "2024/12/01" or "01-12-2024"
        return date('Y-m-d', strtotime($value));
    }
}
