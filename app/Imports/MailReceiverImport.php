<?php

namespace App\Imports;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\MailReceiver;
use Carbon\Carbon;

class MailReceiverImport implements ToModel,WithHeadingRow
{

    public function model(array $row){
        $userData = auth()->user();

        return new MailReceiver([
            "name"=>$row['name'],
            "email"=>$row['email'],
            "category"=>$row['category'],
            "description"=>null,
            "user_id"=>$userData->id,
            "created_by"=>$userData->id,
            "institution_id"=>$userData->institution_id,
            "branch_id"=>$userData->branch_id,
            "created_on"=>Carbon::now(),
        ]);
    }

}
