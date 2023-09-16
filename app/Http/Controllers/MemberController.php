<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\Http\Request;

class MemberController extends Controller
{

    public function createMember(Request $request){
        $member = new Member();
        $member->first_name = $request->first_name ;
        $member->last_name = $request->last_name ;
        $member->other_name = $request->other_name ;
        $member->phone_number = $request->phone_number ;
        $member->member_number = $request->phone_number ;
        $member->alt_member_number = $request->phone_number ;
        $member->gender = $request->gender ;
        $member->date_of_birth = $request->date_of_birth ;
        $member->address = $request->address ;
        $member->city_id = $request->city_id ;
        $member->institution_id = 1;
        $member->branch_id = 1 ;
        $member->status = $request->status ;
        $member->street = $request->street ;
        $member->p_o_box = $request->p_o_box ;
        $member->description = $request->description ;
        $member->created_by = 1 ;
        $member->created_on = now() ;
        $member->save();
        return $this->genericResponse(true, "Member crated successfully", 201, $member);
    }

    public function getMembers(){
        $members = Member::all();
        return $this->genericResponse(true, "Member retrieved successfully", 201, $members);
    }
}
