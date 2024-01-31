<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\TempMember;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Institution;
use App\Models\MemberBatch;


class MembersImport implements ToModel,WithHeadingRow
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
        $memberBatch= MemberBatch::find($this->batchId);
        $memberBatch->institution_id=$instData->id;
        $memberBatch->save();

        return new TempMember([
            'first_name'=> $row['first_name'],
            'last_name'=> $row['last_name'],
            'other_name'=> $row['other_names'],
            'phone_number'=> $row['phone_number'],
            'alt_member_number'=> $row['alt_member_number'],
            'institution_id'=> $instData->id,
            'gender'=> $row['gender'],
            'date_of_birth'=> $row['date_of_birth'],
            'address'=> $row['address'],
            'batch_id'=> $this->batchId,
            'branch_id'=>$branchData->id,
            'status'=> 'Pending',
            'street'=> $row['street'],
            'description'=> $row['description'],
            'institution_ref'=> $row['institution_ref'],
            'created_on'=> now(),
        ]);
    }
}
