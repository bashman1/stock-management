<?php

namespace App\Services;
use App\Models\ComissionConfig;
use App\Models\Comission;

class CommissionConfigService {

    public function createCommissionConfig($request){
        $config = new ComissionConfig();
        $config->name = $request->name;
        $config->amount = $request->amount;
        $config->commission_type = $request->commission_type;
        $config->institution_id = $request->institution_id;
        $config->branch_id = $request->branch_id;
        $config->user_id = $request->user_id;
        $config->status = $request->status;
        $config->created_by = $request->created_by;
        $config->created_on = $request->created_on;
        $config->save();
        return $config;
    }

    public function collectCommission($request){
        $commission = new Comission();
        $commission->tran_id = $request->tran_id ;
        $commission->amount = $request->amount ;
        $commission->commission_config_id = $request->commission_config_id ;
        $commission->institution_id = $request->institution_id ;
        $commission->branch_id = $request->branch_id ;
        $commission->user_id = $request->user_id ;
        $commission->status = $request->status ;
        $commission->created_by = $request->created_by ;
        $commission->created_on = $request->created_on ;
        $commission->save();
        return $commission;
    }
}
