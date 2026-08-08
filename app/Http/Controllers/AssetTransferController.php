<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use App\Models\AssetTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetTransferController extends Controller
{
    /**
     * Transfer an asset to another branch/location. Updates the asset's current
     * branch + location and appends a transfer history row. No GL movement.
     */
    public function createTransfer(Request $request)
    {
        $userData = $this->getUserLogin();
        $asset = FixedAsset::where(['id' => $request->asset_id, 'institution_id' => $userData->institution_id])->first();
        if (!isset($asset)) {
            return $this->genericResponse(false, 'Asset not found', 400, null, 'createTransfer', $request);
        }

        $toBranchId = $request->to_branch_id ?: $asset->branch_id;
        $toLocation = $request->to_location;
        if ($toBranchId == $asset->branch_id && (string) $toLocation === (string) $asset->location) {
            return $this->genericResponse(false, 'Destination is the same as the current location', 400, null, 'createTransfer', $request);
        }

        DB::beginTransaction();
        try {
            $transfer = AssetTransfer::create([
                'asset_id' => $asset->id,
                'from_branch_id' => $asset->branch_id,
                'to_branch_id' => $toBranchId,
                'from_location' => $asset->location,
                'to_location' => $toLocation,
                'transfer_date' => $request->transfer_date ?? now(),
                'reason' => $request->reason,
                'transferred_by' => $userData->id,
                'status' => 'Active',
                'institution_id' => $userData->institution_id,
                'branch_id' => $asset->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ]);

            $asset->branch_id = $toBranchId;
            $asset->location = $toLocation;
            $asset->updated_by = $userData->id;
            $asset->updated_on = now();
            $asset->save();

            DB::commit();
            return $this->genericResponse(true, 'Asset transferred successfully', 201, $transfer, 'createTransfer', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'createTransfer', $request);
        }
    }

    public function getTransfers(Request $request)
    {
        $userData = $this->getUserLogin();
        $q = DB::table('asset_transfers as t')
            ->join('fixed_assets as a', 'a.id', '=', 't.asset_id')
            ->leftJoin('branches as fb', 'fb.id', '=', 't.from_branch_id')
            ->leftJoin('branches as tb', 'tb.id', '=', 't.to_branch_id')
            ->where('t.institution_id', $userData->institution_id)
            ->select('t.*', 'a.name as asset_name', 'a.ref_no as asset_ref', 'fb.name as from_branch', 'tb.name as to_branch');
        if (!empty($request->asset_id)) {
            $q->where('t.asset_id', $request->asset_id);
        }
        $transfers = $q->orderBy('t.id', 'desc')->get();
        return $this->genericResponse(true, 'Asset transfers fetched successfully', 200, $transfers, 'getTransfers', $request);
    }
}
