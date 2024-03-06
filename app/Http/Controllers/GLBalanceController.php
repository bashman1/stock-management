<?php

namespace App\Http\Controllers;

use App\Models\GLBalance;
use Illuminate\Http\Request;
use App\Models\GlType;
use App\Models\GlAcctBk;
use App\Models\GlGenerateAccount;
use Illuminate\Support\Facades\DB;

class GLBalanceController extends Controller
{
  
    /**
     * select * from gl_types
     * loop for each of the type 
     * get the acct_no convert into a number 
     * add plus one 
     * insert into the gl_account_bks 
     */

     public function generateGlAcctBks(){
      DB::beginTransaction();
        $glTypes=GlType::all();
        foreach ($glTypes as $value) {
            $acctInNumber = (double)$value['gl_no'];
            $acctInNumber=$acctInNumber+1;

            $glAcctBk = new GlGenerateAccount();
            $glAcctBk->gl_no = $acctInNumber;
            $glAcctBk->const = $acctInNumber;
            $glAcctBk->current = 1;
            $glAcctBk->step = 1;
            $glAcctBk->description = $value['description'];
            $glAcctBk->gl_cat_no = $value['gl_cat_no'];
            $glAcctBk->gl_sub_cat_no = $value['gl_sub_cat_no'];
            $glAcctBk->gl_type_no = $value['gl_no'];
            $glAcctBk->acct_type = $value['acct_type'];
            $glAcctBk->status = $value['status'];
            $glAcctBk->created_on = now();
            $glAcctBk->save();
        }
        DB::commit();
        return $this->genericResponse(true, "GL acct bk created successfully", 201,[]);
    }
}
