<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\TempMember;
use Illuminate\Http\Request;
use App\Models\MemberNoConfig;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{

    public function createMember(Request $request){
        $userData = auth()->user();
        $request->institution_id= $userData->institution_id;
        $request->branch_id= $userData->branch_id;
        $request->user_id= $userData->id;
        $member=$this->newMember($request);
        return $this->genericResponse(true, "Member crated successfully", 201, $member);
    }

    public function getMembers(){
        $members = Member::all();
        return $this->genericResponse(true, "Member retrieved successfully", 201, $members);
    }

    public function newMember($request){
        DB::beginTransaction();
        $config = MemberNoConfig::where('institution_id', $request->institution_id)->first();
        $config->current_value=$config->current_value+1;
        $config->save();
        $memberNo = $config->prefix.$config->institution_id_code.($config->start_from+$config->current_value);

        $member = new Member();
        $member->first_name = $request->first_name ;
        $member->last_name = $request->last_name ;
        $member->other_name = $request->other_name ;
        $member->phone_number = $request->phone_number ;
        $member->member_number = $memberNo;
        $member->alt_member_number = $request->phone_number ;
        $member->gender = $request->gender ;
        $member->date_of_birth = $request->date_of_birth ;
        $member->address = $request->address ;
        $member->city_id = $request->city_id ;
        $member->institution_id = $request->institution_id;
        $member->branch_id = $request->branch_id;
        $member->status = $request->status ;
        $member->street = $request->street ;
        $member->p_o_box = $request->p_o_box ;
        $member->description = $request->description ;
        $member->created_by = $request->user_id;
        $member->created_on = now();
        $member->save();
        DB::commit();
        return $member;
    }

    public function approveBulkMembers(Request  $request){
        foreach ($request->members as $member) {
            $newMember= TempMember::find($member["id"]);
            $newMember->user_id=$newMember->created_by;
            $newMember->status="Active";
            $response = $this->newMember($newMember);
            if ($response->id){
                unset($newMember->user_id);
                $newMember->save();
            }
        }
        return $this->genericResponse(true, "Approved Successfully", 200, []);
    }

    public function getMembersByInstitution(Request $request){
        $userData = auth()->user();
        $members=DB::select("SELECT M.id, CONCAT(M.first_name,' ',M.last_name,' ',M.other_name) AS name, M.phone_number,
            M.member_number, M.alt_member_number FROM members M WHERE (M.first_name ILIKE '%".$request->searchKey."%'
            OR M.last_name ILIKE '%".$request->searchKey."%' OR M.other_name ILIKE '%".$request->searchKey."%' OR
            M.alt_member_number ILIKE '%".$request->searchKey."%' OR M.member_number ILIKE '%".$request->searchKey."%')
            AND M.institution_id=$userData->institution_id AND status='".$request->status."' ");
        return $this->genericResponse(true, "Approved Successfully", 200, $members);
    }
}
