<?php

namespace App\Http\Controllers;

use App\Models\DepreciationRate;
use Illuminate\Http\Request;

class DepreciationRateController extends Controller
{
    public function createDepreciationRate(Request $request)
    {
        $userData = $this->getUserLogin();
        if (empty($request->name) || (float) $request->rate <= 0) {
            return $this->genericResponse(false, 'A name and a rate greater than 0 are required', 400, null, 'createDepreciationRate', $request);
        }

        $rate = DepreciationRate::create([
            'name' => $request->name,
            'rate' => (float) $request->rate,
            'description' => $request->description,
            'duration_id' => $request->duration_id,
            'status' => 'Active',
            'institution_id' => $userData->institution_id,
            'branch_id' => $userData->branch_id,
            'created_by' => $userData->id,
            'created_on' => now(),
        ]);

        return $this->genericResponse(true, 'Depreciation rate created successfully', 201, $rate, 'createDepreciationRate', $request);
    }

    public function getDepreciationRates()
    {
        $rates = DepreciationRate::with('duration')->where('status', 'Active')->orderBy('id', 'asc')->get();
        return $this->genericResponse(true, 'Depreciation rates fetched successfully', 200, $rates, 'getDepreciationRates', []);
    }
}
