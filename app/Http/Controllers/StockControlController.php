<?php

namespace App\Http\Controllers;

use App\Models\InventoryCostLayer;
use App\Models\StockAdjustment;
use App\Models\StockAdjustmentItem;
use App\Models\Product;
use App\Models\stock;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockControlController extends Controller
{
    /**
     * The open cost layers for a product in the current branch, plus the on-hand
     * quantity, total value and average cost.
     */
    public function getCostLayers(Request $request)
    {
        $userData = $this->getUserLogin();
        $layers = InventoryCostLayer::where([
            'product_id' => $request->product_id, 'branch_id' => $userData->branch_id, 'status' => 'Active',
        ])->where('remaining_quantity', '>', 0)->orderBy('receipt_date', 'asc')->orderBy('id', 'asc')->get();

        $qty = (float) $layers->sum('remaining_quantity');
        $value = 0;
        foreach ($layers as $l) {
            $value += (float) $l->remaining_quantity * (float) $l->unit_cost;
        }
        $product = Product::find($request->product_id);

        return $this->genericResponse(true, 'Cost layers fetched successfully', 200, [
            'layers' => $layers,
            'on_hand' => $qty,
            'total_value' => $value,
            'average_cost' => $qty > 0 ? $value / $qty : 0,
            'valuation_method' => $product->valuation_method ?? 'WAVG',
            'standard_cost' => (float) ($product->standard_cost ?? 0),
        ], 'getCostLayers', $request);
    }

    /**
     * Manual stock adjustment. IN receives a cost layer (Dr Inventory / Cr Stock
     * Adjustment); OUT issues stock by the valuation method (Dr Stock Adjustment /
     * Cr Inventory). One balanced posting per adjustment.
     */
    public function createStockAdjustment(Request $request)
    {
        $userData = $this->getUserLogin();
        $type = strtoupper($request->type ?? '');
        $items = $request->items;
        if (!in_array($type, ['IN', 'OUT']) || !is_array($items) || count($items) < 1) {
            return $this->genericResponse(false, 'A direction (IN/OUT) and at least one product line are required', 400, null, 'createStockAdjustment', $request);
        }

        $branch = Branch::find($userData->branch_id);
        $inv = $this->resolveControlAccount('STI', $userData->institution_id, $branch->code);
        $sad = $this->resolveControlAccount('SAD', $userData->institution_id, $branch->code);
        if (!$inv || !$sad) {
            return $this->genericResponse(false, 'Inventory or Stock Adjustment control account is not configured', 400, null, 'createStockAdjustment', $request);
        }

        // validate OUT availability up front
        foreach ($items as $it) {
            $qty = (float) ($it['quantity'] ?? 0);
            if (empty($it['product_id']) || $qty <= 0) {
                return $this->genericResponse(false, 'Each line needs a product and a positive quantity', 400, null, 'createStockAdjustment', $request);
            }
            if ($type === 'OUT') {
                $st = stock::where(['product_id' => $it['product_id'], 'branch_id' => $userData->branch_id])->first();
                if (!$st || (float) $st->quantity < $qty) {
                    $name = Product::find($it['product_id'])->name ?? ('product ' . $it['product_id']);
                    return $this->genericResponse(false, "Not enough stock of $name to write off (" . (float) ($st->quantity ?? 0) . ' on hand)', 400, null, 'createStockAdjustment', $request);
                }
            }
        }

        DB::beginTransaction();
        try {
            $tranId = $this->generateUuid();
            $ref = 'ADJ-' . strtoupper(substr(str_replace('-', '', $tranId), 0, 8));
            $adj = StockAdjustment::create([
                'ref_no' => $ref, 'type' => $type, 'reason' => $request->reason, 'total_cost' => 0,
                'tran_id' => $tranId, 'status' => 'Active', 'adjustment_date' => $request->adjustment_date ?? now(),
                'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
                'created_by' => $userData->id, 'created_on' => now(),
            ]);

            $total = 0;
            foreach ($items as $it) {
                $pid = $it['product_id'];
                $qty = (float) $it['quantity'];
                $st = stock::where(['product_id' => $pid, 'branch_id' => $userData->branch_id])->first();

                if ($type === 'IN') {
                    $actualCost = (float) ($it['unit_cost'] ?? ($st->purchase_price ?? 0));
                    if ($st) {
                        $st->quantity = (float) $st->quantity + $qty;
                        $st->save();
                    }
                    $this->adjustWarehouseStock($pid, $userData->branch_id, $qty);
                    $this->receiveStock($pid, $userData->branch_id, $qty, $actualCost, 'ADJUSTMENT', $ref, now(), $userData, $tranId);
                    // value the write-on at the carrying cost (standard for STANDARD-cost
                    // products, else the supplied cost) so the GL matches the layers
                    $prod = Product::find($pid);
                    $unitCost = (($prod->valuation_method ?? 'WAVG') === 'STANDARD') ? (float) $prod->standard_cost : $actualCost;
                    $lineCost = $unitCost * $qty;
                } else { // OUT
                    $st->quantity = (float) $st->quantity - $qty;
                    $st->save();
                    $this->adjustWarehouseStock($pid, $userData->branch_id, -$qty);
                    $lineCost = $this->issueStock($pid, $userData->branch_id, $qty, $userData, 'ADJUSTMENT', $ref, $tranId);
                    $unitCost = $qty > 0 ? $lineCost / $qty : 0;
                }

                StockAdjustmentItem::create([
                    'stock_adjustment_id' => $adj->id, 'product_id' => $pid, 'quantity' => $qty,
                    'unit_cost' => $unitCost, 'total_cost' => $lineCost,
                    'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
                    'created_on' => now(),
                ]);
                $total += $lineCost;
            }

            // one balanced posting for the whole adjustment
            if ($total != 0) {
                if ($type === 'IN') {
                    $this->postJournal($inv->acct_no, $inv->acct_type, $sad->acct_no, $sad->acct_type, $total, $tranId, "Stock adjustment IN $ref", now(), $userData, 'ADJ', 'Stock Adjustment');
                } else {
                    $this->postJournal($sad->acct_no, $sad->acct_type, $inv->acct_no, $inv->acct_type, $total, $tranId, "Stock adjustment OUT $ref", now(), $userData, 'ADJ', 'Stock Adjustment');
                }
            }

            $adj->total_cost = $total;
            $adj->save();

            DB::commit();
            return $this->genericResponse(true, 'Stock adjustment posted successfully', 201, $adj->load('items.product'), 'createStockAdjustment', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'createStockAdjustment', $request);
        }
    }

    public function getStockAdjustments(Request $request)
    {
        $userData = $this->getUserLogin();
        $adjustments = StockAdjustment::with('items.product')
            ->where(['institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id])
            ->orderBy('id', 'desc')->get();
        return $this->genericResponse(true, 'Stock adjustments fetched successfully', 200, $adjustments, 'getStockAdjustments', $request);
    }
}
