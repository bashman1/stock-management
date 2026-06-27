<?php

namespace App\Http\Controllers;

use App\Models\Bom;
use App\Models\BomItem;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderMaterial;
use App\Models\ProductionMovement;
use App\Models\Product;
use App\Models\stock;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManufacturingController extends Controller
{
    // ---------------------------------------------------------------- BOM ----

    /**
     * Create a Bill of Materials: a finished product plus the materials (and
     * quantities) needed to make `output_quantity` units of it.
     */
    public function createBom(Request $request)
    {
        $userData = $this->getUserLogin();
        $items = $request->items;
        if (empty($request->product_id) || !is_array($items) || count($items) < 1) {
            return $this->genericResponse(false, 'A finished product and at least one material are required', 400, null, 'createBom', $request);
        }
        $product = Product::find($request->product_id);
        if (!$product) {
            return $this->genericResponse(false, 'Finished product not found', 400, null, 'createBom', $request);
        }

        DB::beginTransaction();
        try {
            $bom = Bom::create([
                'product_id' => $request->product_id,
                'name' => $request->name ?: ($product->name . ' BOM'),
                'version' => (int) ($request->version ?: 1),
                'output_quantity' => (float) ($request->output_quantity ?: 1) ?: 1,
                'is_active' => true,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'status' => 'Active',
                'created_by' => $userData->id,
                'created_on' => now(),
            ]);

            foreach ($items as $it) {
                $matId = $it['material_product_id'] ?? $it['id'] ?? null;
                $qty = (float) ($it['quantity'] ?? 0);
                if (empty($matId) || $qty <= 0) {
                    continue;
                }
                BomItem::create([
                    'bom_id' => $bom->id,
                    'material_product_id' => $matId,
                    'quantity' => $qty,
                    'measurement_unit_id' => $it['measurement_unit_id'] ?? null,
                    'scrap_percent' => (float) ($it['scrap_percent'] ?? 0),
                    'institution_id' => $userData->institution_id,
                    'branch_id' => $userData->branch_id,
                    'created_by' => $userData->id,
                    'created_on' => now(),
                ]);
            }

            DB::commit();
            return $this->genericResponse(true, 'Bill of materials created successfully', 201, $bom->load('items'), 'createBom', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'createBom', $request);
        }
    }

    public function getBoms(Request $request)
    {
        $userData = $this->getUserLogin();
        $boms = Bom::with(['items.material', 'product'])
            ->where(['institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id, 'status' => 'Active'])
            ->orderBy('id', 'desc')->get();
        $boms->transform(function ($b) {
            $b->product_name = $b->product->name ?? null;
            $b->item_count = $b->items->count();
            return $b;
        });
        return $this->genericResponse(true, 'Bills of materials fetched successfully', 200, $boms, 'getBoms', $request);
    }

    public function getBom(Request $request)
    {
        $userData = $this->getUserLogin();
        $bom = Bom::with(['items.material', 'product'])
            ->where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!$bom) {
            return $this->genericResponse(false, 'BOM not found', 400, null, 'getBom', $request);
        }
        return $this->genericResponse(true, 'BOM fetched successfully', 200, $bom, 'getBom', $request);
    }

    // -------------------------------------------------- Production orders ----

    /**
     * Raise a production order from a BOM. Explodes the BOM into the materials the
     * order will need. No stock or GL movement yet (status Draft).
     */
    public function createProductionOrder(Request $request)
    {
        $userData = $this->getUserLogin();
        $qty = (float) $request->quantity;
        $bom = Bom::with('items')->where(['id' => $request->bom_id, 'institution_id' => $userData->institution_id])->first();
        if (!$bom) {
            return $this->genericResponse(false, 'BOM not found', 400, null, 'createProductionOrder', $request);
        }
        if ($qty <= 0) {
            return $this->genericResponse(false, 'Quantity to produce must be greater than 0', 400, null, 'createProductionOrder', $request);
        }
        $factor = $qty / ((float) $bom->output_quantity ?: 1);

        DB::beginTransaction();
        try {
            $order = ProductionOrder::create([
                'order_no' => 'PO-' . strtoupper(substr(str_replace('-', '', $this->generateUuid()), 0, 8)),
                'bom_id' => $bom->id,
                'product_id' => $bom->product_id,
                'quantity' => $qty,
                'quantity_completed' => 0,
                'quantity_scrapped' => 0,
                'wip_cost' => 0,
                'status' => 'Draft',
                'scheduled_date' => $request->scheduled_date ?? now(),
                'tran_id' => null,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ]);

            foreach ($bom->items as $item) {
                $required = (float) $item->quantity * $factor * (1 + ((float) $item->scrap_percent / 100));
                $matStock = stock::where(['product_id' => $item->material_product_id, 'branch_id' => $userData->branch_id])->first();
                ProductionOrderMaterial::create([
                    'production_order_id' => $order->id,
                    'material_product_id' => $item->material_product_id,
                    'quantity_required' => $required,
                    'quantity_consumed' => 0,
                    'unit_cost' => (float) ($matStock->purchase_price ?? 0),
                    'institution_id' => $userData->institution_id,
                    'branch_id' => $userData->branch_id,
                    'created_by' => $userData->id,
                    'created_on' => now(),
                ]);
            }

            DB::commit();
            return $this->genericResponse(true, 'Production order created successfully', 201, $order->load('materials.material'), 'createProductionOrder', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'createProductionOrder', $request);
        }
    }

    public function getProductionOrders(Request $request)
    {
        $userData = $this->getUserLogin();
        $orders = ProductionOrder::with('product')
            ->where(['institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id])
            ->orderBy('id', 'desc')->get();
        $orders->transform(function ($o) {
            $o->product_name = $o->product->name ?? null;
            return $o;
        });
        return $this->genericResponse(true, 'Production orders fetched successfully', 200, $orders, 'getProductionOrders', $request);
    }

    public function getProductionOrder(Request $request)
    {
        $userData = $this->getUserLogin();
        $order = ProductionOrder::with(['materials.material', 'movements', 'product'])
            ->where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!$order) {
            return $this->genericResponse(false, 'Production order not found', 400, null, 'getProductionOrder', $request);
        }
        return $this->genericResponse(true, 'Production order fetched successfully', 200, $order, 'getProductionOrder', $request);
    }

    /**
     * Issue the order's materials into work-in-progress. Decrements each material's
     * stock (and warehouse ledger) and posts one Dr WIP / Cr Inventory for the
     * total material cost. Order moves to InProgress.
     */
    public function issueMaterials(Request $request)
    {
        $userData = $this->getUserLogin();
        $order = ProductionOrder::with('materials')->where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!$order) {
            return $this->genericResponse(false, 'Production order not found', 400, null, 'issueMaterials', $request);
        }
        if ($order->status !== 'Draft') {
            return $this->genericResponse(false, 'Materials can only be issued for a Draft order', 400, null, 'issueMaterials', $request);
        }

        $branch = Branch::find($userData->branch_id);
        $wip = $this->resolveControlAccount('WIP', $userData->institution_id, $branch->code);
        $inv = $this->resolveControlAccount('STI', $userData->institution_id, $branch->code);
        if (!$wip || !$inv) {
            return $this->genericResponse(false, 'WIP or Inventory control account is not configured', 400, null, 'issueMaterials', $request);
        }

        // verify availability before moving anything
        foreach ($order->materials as $m) {
            $st = stock::where(['product_id' => $m->material_product_id, 'branch_id' => $userData->branch_id])->first();
            if (!$st || (float) $st->quantity < (float) $m->quantity_required) {
                $name = Product::find($m->material_product_id)->name ?? ('product ' . $m->material_product_id);
                return $this->genericResponse(false, "Not enough stock of $name (" . (float) ($st->quantity ?? 0) . ' available, ' . (float) $m->quantity_required . ' needed)', 400, null, 'issueMaterials', $request);
            }
        }

        DB::beginTransaction();
        try {
            $tranId = $this->generateUuid();
            $totalCost = 0;
            foreach ($order->materials as $m) {
                $qty = (float) $m->quantity_required;
                $st = stock::where(['product_id' => $m->material_product_id, 'branch_id' => $userData->branch_id])->first();
                $unitCost = (float) $st->purchase_price;
                $cost = $unitCost * $qty;

                $st->quantity = (float) $st->quantity - $qty;
                $st->save();
                $this->adjustWarehouseStock($m->material_product_id, $userData->branch_id, -$qty);

                $m->quantity_consumed = $qty;
                $m->unit_cost = $unitCost;
                $m->updated_on = now();
                $m->save();

                $totalCost += $cost;
                ProductionMovement::create([
                    'production_order_id' => $order->id, 'type' => 'ISSUE',
                    'product_id' => $m->material_product_id, 'quantity' => $qty, 'cost' => $cost,
                    'tran_id' => $tranId, 'ref_no' => $order->order_no, 'note' => 'Material issued to WIP',
                    'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
                    'created_by' => $userData->id, 'created_on' => now(),
                ]);
            }

            if ($totalCost > 0) {
                $this->postJournal(
                    $wip->acct_no, $wip->acct_type, $inv->acct_no, $inv->acct_type,
                    $totalCost, $tranId, "Issue materials to production $order->order_no",
                    now(), $userData, 'PRD', 'Production Issue'
                );
            }

            $order->wip_cost = $totalCost;
            $order->status = 'InProgress';
            $order->started_on = now();
            $order->tran_id = $tranId;
            $order->updated_by = $userData->id;
            $order->updated_on = now();
            $order->save();

            DB::commit();
            return $this->genericResponse(true, 'Materials issued to production successfully', 200, $order->load('materials.material'), 'issueMaterials', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'issueMaterials', $request);
        }
    }

    /**
     * Write off part of the work-in-progress as scrap: Dr Scrap Loss / Cr WIP for
     * the scrapped output's share of the WIP cost.
     */
    public function recordScrap(Request $request)
    {
        $userData = $this->getUserLogin();
        $scrapQty = (float) $request->quantity;
        $order = ProductionOrder::where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!$order) {
            return $this->genericResponse(false, 'Production order not found', 400, null, 'recordScrap', $request);
        }
        if ($order->status !== 'InProgress') {
            return $this->genericResponse(false, 'Scrap can only be recorded while the order is In Progress', 400, null, 'recordScrap', $request);
        }
        $remaining = (float) $order->quantity - (float) $order->quantity_completed - (float) $order->quantity_scrapped;
        if ($scrapQty <= 0 || $scrapQty > $remaining) {
            return $this->genericResponse(false, "Scrap quantity must be between 0 and the remaining $remaining units", 400, null, 'recordScrap', $request);
        }

        $branch = Branch::find($userData->branch_id);
        $wip = $this->resolveControlAccount('WIP', $userData->institution_id, $branch->code);
        $psl = $this->resolveControlAccount('PSL', $userData->institution_id, $branch->code);
        if (!$wip || !$psl) {
            return $this->genericResponse(false, 'WIP or Scrap Loss control account is not configured', 400, null, 'recordScrap', $request);
        }

        DB::beginTransaction();
        try {
            $unitCost = $remaining > 0 ? ((float) $order->wip_cost / $remaining) : 0;
            $scrapCost = $unitCost * $scrapQty;
            $tranId = $this->generateUuid();

            if ($scrapCost > 0) {
                $this->postJournal(
                    $psl->acct_no, $psl->acct_type, $wip->acct_no, $wip->acct_type,
                    $scrapCost, $tranId, "Scrap on production $order->order_no",
                    now(), $userData, 'PRD', 'Production Scrap'
                );
            }

            $order->wip_cost = (float) $order->wip_cost - $scrapCost;
            $order->quantity_scrapped = (float) $order->quantity_scrapped + $scrapQty;
            $order->updated_by = $userData->id;
            $order->updated_on = now();
            $order->save();

            ProductionMovement::create([
                'production_order_id' => $order->id, 'type' => 'SCRAP',
                'product_id' => $order->product_id, 'quantity' => $scrapQty, 'cost' => $scrapCost,
                'tran_id' => $tranId, 'ref_no' => $order->order_no, 'note' => $request->note ?: 'Scrap',
                'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
                'created_by' => $userData->id, 'created_on' => now(),
            ]);

            DB::commit();
            return $this->genericResponse(true, 'Scrap recorded successfully', 200, $order, 'recordScrap', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'recordScrap', $request);
        }
    }

    /**
     * Complete production: receive finished goods out of WIP. Increments the
     * finished product's stock (weighted-average cost) and posts Dr Inventory /
     * Cr WIP. Completes the order when nothing is left to produce.
     */
    public function completeProduction(Request $request)
    {
        $userData = $this->getUserLogin();
        $doneQty = (float) $request->quantity_completed;
        $order = ProductionOrder::where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!$order) {
            return $this->genericResponse(false, 'Production order not found', 400, null, 'completeProduction', $request);
        }
        if ($order->status !== 'InProgress') {
            return $this->genericResponse(false, 'Only an In Progress order can be completed', 400, null, 'completeProduction', $request);
        }
        $remaining = (float) $order->quantity - (float) $order->quantity_completed - (float) $order->quantity_scrapped;
        if ($doneQty <= 0 || $doneQty > $remaining) {
            return $this->genericResponse(false, "Completed quantity must be between 0 and the remaining $remaining units", 400, null, 'completeProduction', $request);
        }

        $branch = Branch::find($userData->branch_id);
        $wip = $this->resolveControlAccount('WIP', $userData->institution_id, $branch->code);
        $inv = $this->resolveControlAccount('STI', $userData->institution_id, $branch->code);
        if (!$wip || !$inv) {
            return $this->genericResponse(false, 'WIP or Inventory control account is not configured', 400, null, 'completeProduction', $request);
        }

        DB::beginTransaction();
        try {
            $unitCost = $remaining > 0 ? ((float) $order->wip_cost / $remaining) : 0;
            $fgCost = $unitCost * $doneQty;
            $tranId = $this->generateUuid();

            // receive finished goods into the finished product's stock (weighted average)
            $fgStock = stock::where(['product_id' => $order->product_id, 'branch_id' => $userData->branch_id])->first();
            if (!$fgStock) {
                $fgStock = new stock();
                $fgStock->product_id = $order->product_id;
                $fgStock->quantity = 0;
                $fgStock->purchase_price = 0;
                $fgStock->selling_price = 0;
                $fgStock->institution_id = $userData->institution_id;
                $fgStock->branch_id = $userData->branch_id;
                $fgStock->user_id = $userData->id;
                $fgStock->status = 'Active';
                $fgStock->created_by = $userData->id;
                $fgStock->created_on = now();
            }
            $oldQty = (float) $fgStock->quantity;
            $newQty = $oldQty + $doneQty;
            $fgStock->purchase_price = $newQty > 0
                ? (($oldQty * (float) $fgStock->purchase_price) + $fgCost) / $newQty
                : $unitCost;
            $fgStock->quantity = $newQty;
            $fgStock->updated_by = $userData->id;
            $fgStock->updated_on = now();
            $fgStock->save();
            $this->adjustWarehouseStock($order->product_id, $userData->branch_id, $doneQty);

            if ($fgCost > 0) {
                $this->postJournal(
                    $inv->acct_no, $inv->acct_type, $wip->acct_no, $wip->acct_type,
                    $fgCost, $tranId, "Finished goods received from production $order->order_no",
                    now(), $userData, 'PRD', 'Production Complete'
                );
            }

            $order->wip_cost = (float) $order->wip_cost - $fgCost;
            $order->quantity_completed = (float) $order->quantity_completed + $doneQty;
            if (($remaining - $doneQty) <= 0.00001) {
                $order->status = 'Completed';
                $order->completed_on = now();
            }
            $order->updated_by = $userData->id;
            $order->updated_on = now();
            $order->save();

            ProductionMovement::create([
                'production_order_id' => $order->id, 'type' => 'COMPLETE',
                'product_id' => $order->product_id, 'quantity' => $doneQty, 'cost' => $fgCost,
                'tran_id' => $tranId, 'ref_no' => $order->order_no, 'note' => 'Finished goods received',
                'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
                'created_by' => $userData->id, 'created_on' => now(),
            ]);

            DB::commit();
            return $this->genericResponse(true, 'Production completed successfully', 200, $order->load('product'), 'completeProduction', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'completeProduction', $request);
        }
    }

    public function cancelProductionOrder(Request $request)
    {
        $userData = $this->getUserLogin();
        $order = ProductionOrder::where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!$order) {
            return $this->genericResponse(false, 'Production order not found', 400, null, 'cancelProductionOrder', $request);
        }
        if ($order->status !== 'Draft') {
            return $this->genericResponse(false, 'Only a Draft order can be cancelled (issue/complete activity cannot be undone here)', 400, null, 'cancelProductionOrder', $request);
        }
        $order->status = 'Cancelled';
        $order->updated_by = $userData->id;
        $order->updated_on = now();
        $order->save();
        return $this->genericResponse(true, 'Production order cancelled', 200, $order, 'cancelProductionOrder', $request);
    }
}
