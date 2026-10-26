<?php

namespace App\Http\Controllers;

use App\Models\FixedAsset;
use App\Models\AssetMaintenance;
use App\Models\GlAccounts;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetMaintenanceController extends Controller
{
    /**
     * Log a maintenance/service event for an asset AND post it as an expense:
     *   Dr <maintenance expense account>  /  Cr Cash|Bank|Accounts Payable
     */
    public function createMaintenance(Request $request)
    {
        $userData = $this->getUserLogin();
        $branch = Branch::find($userData->branch_id);
        if (!isset($branch)) {
            return $this->genericResponse(false, 'Branch not found', 400, null, 'createMaintenance', $request);
        }

        $asset = FixedAsset::where(['id' => $request->asset_id, 'institution_id' => $userData->institution_id])->first();
        if (!isset($asset)) {
            return $this->genericResponse(false, 'Asset not found', 400, null, 'createMaintenance', $request);
        }

        $method = strtoupper((string) $request->payment_method);
        $cost = (float) $request->cost;
        if ($cost <= 0 || !in_array($method, ['CASH', 'BANK', 'CREDIT'])) {
            return $this->genericResponse(false, 'A positive cost and a valid payment method are required', 400, null, 'createMaintenance', $request);
        }

        // Dr = maintenance expense account: use the one supplied, else fall back to
        // "Repair and maintenance" (5241301) for this branch.
        $expenseAcct = null;
        if (!empty($request->expense_acct_no)) {
            $expenseAcct = GlAccounts::where([
                'acct_no' => $request->expense_acct_no,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
            ])->first();
        }
        if (!isset($expenseAcct)) {
            $expenseAcct = GlAccounts::where([
                'gl_no' => '5241301',
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
            ])->first();
        }
        if (!isset($expenseAcct) || $expenseAcct->acct_type !== 'EXPENSE') {
            return $this->genericResponse(false, 'No valid maintenance expense account', 400, null, 'createMaintenance', $request);
        }

        // Cr = cash / bank / accounts payable
        $payAcct = $this->resolveControlAccount($this->payParamCode($method), $userData->institution_id, $branch->code);
        if (!isset($payAcct)) {
            return $this->genericResponse(false, 'Payment account is not configured for this branch', 400, null, 'createMaintenance', $request);
        }

        $refNo = 'AM-' . strtoupper(substr(str_replace('-', '', $this->generateUuid()), 0, 8));
        $maintDate = $request->maintenance_date ?? now();

        DB::beginTransaction();
        try {
            $tranId = $this->generateUuid();
            $this->postJournal(
                $expenseAcct->acct_no,
                'EXPENSE',
                $payAcct->acct_no,
                $payAcct->acct_type,
                $cost,
                $tranId,
                'Asset maintenance - ' . $asset->name,
                $maintDate,
                $userData,
                'AMT',
                'Asset Maintenance'
            );

            if ($method === 'CREDIT') {
                $this->createPayable((array) [
                    'ref_no' => $refNo,
                    'tran_id' => $tranId,
                    'supplier_id' => $request->supplier_id,
                    'amount' => $cost,
                    'amount_paid' => 0,
                    'status' => 'Active',
                    'institution_id' => $userData->institution_id,
                    'branch_id' => $userData->branch_id,
                    'created_by' => $userData->id,
                    'created_on' => now(),
                ]);
            }

            $maintenance = AssetMaintenance::create([
                'asset_id' => $asset->id,
                'ref_no' => $refNo,
                'maintenance_date' => $maintDate,
                'type' => $request->type,
                'description' => $request->description,
                'cost' => $cost,
                'payee' => $request->payee,
                'expense_acct_no' => $expenseAcct->acct_no,
                'payment_method' => $method,
                'pay_acct_no' => $payAcct->acct_no,
                'tran_id' => $tranId,
                'status' => 'Active',
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ]);

            DB::commit();
            return $this->genericResponse(true, 'Maintenance recorded successfully', 201, $maintenance, 'createMaintenance', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'createMaintenance', $request);
        }
    }

    public function getMaintenances(Request $request)
    {
        $userData = $this->getUserLogin();
        $q = DB::table('asset_maintenances as m')
            ->join('fixed_assets as a', 'a.id', '=', 'm.asset_id')
            ->where('m.institution_id', $userData->institution_id)
            ->select('m.*', 'a.name as asset_name', 'a.ref_no as asset_ref');
        if (!empty($request->asset_id)) {
            $q->where('m.asset_id', $request->asset_id);
        }
        if (!empty($request->from_date)) {
            $q->whereDate('m.maintenance_date', '>=', $request->from_date);
        }
        if (!empty($request->to_date)) {
            $q->whereDate('m.maintenance_date', '<=', $request->to_date);
        }
        $rows = $q->orderBy('m.id', 'desc')->get();
        return $this->genericResponse(true, 'Asset maintenance fetched successfully', 200, $rows, 'getMaintenances', $request);
    }
}
