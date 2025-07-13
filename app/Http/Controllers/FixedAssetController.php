<?php

namespace App\Http\Controllers;

use App\Models\DepreciationRate;
use App\Models\FixedAsset;
use App\Models\PaymentMethod;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FixedAssetController extends Controller
{
    public function getPaymentMethods()
    {
        try {
            $paymentMethods = PaymentMethod::where('status', 'Active')
                ->orderBy('id', 'asc')
                ->get();

            return $this->genericResponse(true, 'Payment methods retrieved successfully', 200, $paymentMethods, 'getPaymentMethods', []);
        } catch (\Exception $e) {
            return $this->genericResponse(false, 'Unauthorized access', 403, [], 'getPaymentMethods', []);
        }
    }


    public function getDepreciationRates()
    {
        try {
            $depreciationRates = DepreciationRate::with('duration')->where('status', 'Active')
                ->orderBy('id', 'asc')
                ->get();

            return $this->genericResponse(true, 'Depreciation rates retrieved successfully', 200, $depreciationRates, 'getDepreciationRates', []);
        } catch (\Exception $e) {
            return $this->genericResponse(false, $e->getMessage(), 403, [], 'getDepreciationRates', []);
        }
    }


    public function createOrUpdateFixedAsset(Request $request)
    {
        try {
            $userData = $this->getUserLogin();

            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'asset_account' => $request->asset_acct,
                'expense_account' => $request->expense_acct,
                'depreciation_rate_id' => $request->depreciation_rate_id,
                'purchase_amount' => $request->purchase,
                'payment_method_id' => $request->payment_method_id,
                'duration_id' => $request->duration_id,
                'purchase_date' => $request->date,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => Carbon::now(),
            ];

            if ($request->id) {
                // Edit/update mode
                $fixedAsset = FixedAsset::find($request->id);
                if (!$fixedAsset) {
                    return $this->genericResponse(false, "Fixed asset not found", 404, [], 'createOrUpdateFixedAsset', $request);
                }
                $fixedAsset->update($data);

                return $this->genericResponse(true, "Fixed asset updated successfully", 200, $fixedAsset, 'createOrUpdateFixedAsset', $request);
            } else {
                // Create mode
                $fixedAsset = FixedAsset::create($data);

                return $this->genericResponse(true, "Fixed asset created successfully", 201, $fixedAsset, 'createOrUpdateFixedAsset', $request);
            }
        } catch (\Exception $e) {
            return $this->genericResponse(false, $e->getMessage(), 500, [], 'createOrUpdateFixedAsset', $request);
        }
    }


    // public function createFixedAsset(Request $request)  {
    //     try {
    //         $userData = $this->getUserLogin();
    //         $fixedAsset = FixedAsset::create([
    //             'name'=>$request->name,
    //             'description'=>$request->description,
    //             'asset_account'=>$request->asset_acct,
    //             'expense_account'=>$request->expense_acct,
    //             'depreciation_rate_id'=>$request->depreciation_rate_id,
    //             'purchase_amount'=>$request->purchase,
    //             'payment_method_id'=>$request->payment_method_id,
    //             'duration_id'=>$request->duration_id,
    //             'purchase_date'=>$request->date,
    //             'institution_id'=> $userData->institution_id,
    //             'branch_id'=> $userData->branch_id,
    //             'created_by'=> $userData->id,
    //             'created_on'=> Carbon::now(),
    //         ]);
    //         return $this->genericResponse(true, "Fixed assets created successfully", 201, $fixedAsset, "createFixedAsset", $request);
    //     } catch (\Exception $e) {
    //         return $this->genericResponse(false, $e->getMessage(), 403, [], 'createFixedAsset', $request);
    //     }
    // }

}
