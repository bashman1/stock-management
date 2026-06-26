<?php

namespace App\Http\Controllers;

use App\Models\DepreciationRate;
use App\Models\FixedAsset;
use App\Models\AssetDepreciation;
use App\Models\PaymentMethod;
use App\Models\GlAccounts;
use App\Models\Branch;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FixedAssetController extends Controller
{
    public function getPaymentMethods()
    {
        $paymentMethods = PaymentMethod::where('status', 'Active')->orderBy('id', 'asc')->get();
        return $this->genericResponse(true, 'Payment methods retrieved successfully', 200, $paymentMethods, 'getPaymentMethods', []);
    }

    public function getDepreciationRates()
    {
        $depreciationRates = DepreciationRate::with('duration')->where('status', 'Active')->orderBy('id', 'asc')->get();
        return $this->genericResponse(true, 'Depreciation rates retrieved successfully', 200, $depreciationRates, 'getDepreciationRates', []);
    }

    /**
     * Create a fixed asset and post its purchase journal:
     *   Dr <asset account>  /  Cr Cash|Bank|Accounts Payable
     */
    public function createFixedAsset(Request $request)
    {
        $userData = $this->getUserLogin();
        $branch = Branch::find($userData->branch_id);
        if (!isset($branch)) {
            return $this->genericResponse(false, 'Branch not found', 400, null, 'createFixedAsset', $request);
        }

        $method = strtoupper((string) $request->payment_method);
        $amount = (float) $request->purchase_amount;
        if (empty($request->asset_account) || $amount <= 0 || !in_array($method, ['CASH', 'BANK', 'CREDIT'])) {
            return $this->genericResponse(false, 'Asset account, a positive amount and a valid payment method are required', 400, null, 'createFixedAsset', $request);
        }

        // Dr = the chosen fixed-asset account (must be ASSET in this branch)
        $assetAcct = GlAccounts::where([
            'acct_no' => $request->asset_account,
            'institution_id' => $userData->institution_id,
            'branch_id' => $userData->branch_id,
        ])->first();
        if (!isset($assetAcct) || $assetAcct->acct_type !== 'ASSET') {
            return $this->genericResponse(false, 'Invalid fixed-asset account', 400, null, 'createFixedAsset', $request);
        }

        // Cr = cash / bank / accounts payable
        $payAcct = $this->resolveControlAccount($this->payParamCode($method), $userData->institution_id, $branch->code);
        if (!isset($payAcct)) {
            return $this->genericResponse(false, 'Payment account is not configured for this branch', 400, null, 'createFixedAsset', $request);
        }

        $depMethod = strtoupper((string) $request->depreciation_method) === 'REDUCING_BALANCE' ? 'REDUCING_BALANCE' : 'STRAIGHT_LINE';
        $salvage = (float) ($request->salvage_value ?? 0);
        $purchaseDate = $request->purchase_date ?? now();
        $refNo = 'FA-' . strtoupper(substr(str_replace('-', '', $this->generateUuid()), 0, 8));

        DB::beginTransaction();
        try {
            $tranId = $this->generateUuid();
            $this->postJournal(
                $assetAcct->acct_no,
                'ASSET',
                $payAcct->acct_no,
                $payAcct->acct_type,
                $amount,
                $tranId,
                'Fixed asset purchase - ' . $request->name,
                $purchaseDate,
                $userData,
                'FAS',
                'Fixed Asset Purchase'
            );

            if ($method === 'CREDIT') {
                $this->createPayable((array) [
                    'ref_no' => $refNo,
                    'tran_id' => $tranId,
                    'supplier_id' => $request->supplier_id,
                    'amount' => $amount,
                    'amount_paid' => 0,
                    'status' => 'Active',
                    'institution_id' => $userData->institution_id,
                    'branch_id' => $userData->branch_id,
                    'created_by' => $userData->id,
                    'created_on' => now(),
                ]);
            }

            $asset = FixedAsset::create([
                'ref_no' => $refNo,
                'name' => $request->name,
                'description' => $request->description,
                'asset_account' => $assetAcct->acct_no,
                'expense_account' => $request->expense_account,
                'supplier_id' => $request->supplier_id,
                'depreciation_rate_id' => $request->depreciation_rate_id,
                'depreciation_method' => $depMethod,
                'purchase_amount' => $amount,
                'salvage_value' => $salvage,
                'accumulated_depreciation' => 0,
                'book_value' => $amount,
                'payment_method' => $method,
                'pay_acct_no' => $payAcct->acct_no,
                'payment_method_id' => $request->payment_method_id,
                'duration_id' => $request->duration_id,
                'purchase_date' => $purchaseDate,
                'location' => $request->location,
                'tran_id' => $tranId,
                'status' => 'Active',
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ]);

            DB::commit();
            return $this->genericResponse(true, 'Fixed asset created successfully', 201, $asset, 'createFixedAsset', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'createFixedAsset', $request);
        }
    }

    public function getFixedAssets(Request $request)
    {
        $userData = $this->getUserLogin();
        $q = FixedAsset::with(['depreciationRate', 'branch'])
            ->where(['institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id]);
        if (!empty($request->status)) {
            $q->where('status', $request->status);
        }
        if (!empty($request->searchKey)) {
            $s = $request->searchKey;
            $q->where(function ($w) use ($s) {
                $w->where('name', 'ILIKE', "%$s%")->orWhere('ref_no', 'ILIKE', "%$s%")->orWhere('location', 'ILIKE', "%$s%");
            });
        }
        $assets = $q->orderBy('id', 'desc')->get();
        return $this->genericResponse(true, 'Fixed assets fetched successfully', 200, $assets, 'getFixedAssets', $request);
    }

    public function getFixedAsset(Request $request)
    {
        $userData = $this->getUserLogin();
        $asset = FixedAsset::with(['depreciationRate', 'branch', 'transfers', 'maintenances', 'depreciations'])
            ->where(['id' => $request->id, 'institution_id' => $userData->institution_id])
            ->first();
        if (!isset($asset)) {
            return $this->genericResponse(false, 'Fixed asset not found', 400, null, 'getFixedAsset', $request);
        }
        return $this->genericResponse(true, 'Fixed asset fetched successfully', 200, $asset, 'getFixedAsset', $request);
    }

    /**
     * Manually run depreciation for the branch's due assets for a period (YYYY-MM).
     */
    public function runDepreciation(Request $request)
    {
        $userData = $this->getUserLogin();
        $period = $request->period ?: now()->format('Y-m');

        $assets = FixedAsset::where([
            'institution_id' => $userData->institution_id,
            'branch_id' => $userData->branch_id,
            'status' => 'Active',
        ])->get();

        $posted = 0;
        $totalDep = 0;
        foreach ($assets as $asset) {
            $result = $this->depreciateAsset($asset, $period);
            if (!empty($result['posted'])) {
                $posted++;
                $totalDep += $result['amount'];
            }
        }

        return $this->genericResponse(true, "Depreciation run complete: $posted asset(s) depreciated", 200, ['period' => $period, 'assets' => $posted, 'total' => $totalDep], 'runDepreciation', $request);
    }

    /**
     * Depreciation engine — posts one period's depreciation for an asset.
     * Idempotent: a given (asset, period) is only ever posted once.
     * Self-contained (derives posting context from the asset) so the artisan
     * command can call it without an authenticated user.
     *
     * @return array{posted:bool, amount:float, reason?:string}
     */
    public function depreciateAsset(FixedAsset $asset, string $period): array
    {
        // already depreciated for this period?
        if (AssetDepreciation::where(['asset_id' => $asset->id, 'period' => $period])->exists()) {
            return ['posted' => false, 'amount' => 0, 'reason' => 'already-run'];
        }

        $rate = optional(DepreciationRate::find($asset->depreciation_rate_id))->rate;
        if (!$rate || (float) $rate <= 0) {
            return ['posted' => false, 'amount' => 0, 'reason' => 'no-rate'];
        }

        $salvage = (float) $asset->salvage_value;
        $bookValue = (float) $asset->book_value;
        $depreciableRemaining = max(0, $bookValue - $salvage);
        if ($depreciableRemaining <= 0) {
            return ['posted' => false, 'amount' => 0, 'reason' => 'fully-depreciated'];
        }

        // monthly depreciation
        if ($asset->depreciation_method === 'REDUCING_BALANCE') {
            $dep = $bookValue * ((float) $rate) / 100 / 12;
        } else { // STRAIGHT_LINE on cost
            $dep = ((float) $asset->purchase_amount - $salvage) * ((float) $rate) / 100 / 12;
        }
        $dep = round(min($dep, $depreciableRemaining), 2);
        if ($dep <= 0) {
            return ['posted' => false, 'amount' => 0, 'reason' => 'zero'];
        }

        $branch = Branch::find($asset->branch_id);
        if (!isset($branch)) {
            return ['posted' => false, 'amount' => 0, 'reason' => 'no-branch'];
        }
        $dex = $this->resolveControlAccount('DEX', $asset->institution_id, $branch->code); // Depreciation Expense (Dr)
        $accDep = $this->resolveControlAccount('AD', $asset->institution_id, $branch->code); // Accumulated Depreciation (Cr)
        if (!isset($dex) || !isset($accDep)) {
            return ['posted' => false, 'amount' => 0, 'reason' => 'accounts-missing'];
        }

        // posting context derived from the asset (works for scheduler + manual run)
        $ctx = (object) [
            'institution_id' => $asset->institution_id,
            'branch_id' => $asset->branch_id,
            'id' => $asset->created_by,
        ];

        DB::beginTransaction();
        try {
            $tranId = $this->generateUuid();
            $runDate = Carbon::createFromFormat('Y-m', $period)->endOfMonth();
            $this->postJournal(
                $dex->acct_no,
                'EXPENSE',
                $accDep->acct_no,
                'ASSET',
                $dep,
                $tranId,
                "Depreciation $period - {$asset->name}",
                $runDate,
                $ctx,
                'DEP',
                'Depreciation'
            );

            $opening = $bookValue;
            $accumulatedAfter = round((float) $asset->accumulated_depreciation + $dep, 2);
            $closing = round((float) $asset->purchase_amount - $accumulatedAfter, 2);

            $asset->accumulated_depreciation = $accumulatedAfter;
            $asset->book_value = $closing;
            $asset->last_depreciation_date = $runDate;
            $asset->save();

            AssetDepreciation::create([
                'asset_id' => $asset->id,
                'period' => $period,
                'method' => $asset->depreciation_method,
                'opening_book_value' => $opening,
                'depreciation_amount' => $dep,
                'accumulated_after' => $accumulatedAfter,
                'closing_book_value' => $closing,
                'run_date' => $runDate,
                'tran_id' => $tranId,
                'status' => 'Active',
                'institution_id' => $asset->institution_id,
                'branch_id' => $asset->branch_id,
                'created_by' => $asset->created_by,
                'created_on' => now(),
            ]);

            DB::commit();
            return ['posted' => true, 'amount' => $dep];
        } catch (\Throwable $th) {
            DB::rollBack();
            return ['posted' => false, 'amount' => 0, 'reason' => $th->getMessage()];
        }
    }
}
