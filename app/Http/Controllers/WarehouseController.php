<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseStock;
use App\Models\WarehouseTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    /**
     * Create a warehouse for the current branch. The first warehouse a branch gets
     * (if none is default yet) is marked default so the sync hooks have a target.
     */
    public function createWarehouse(Request $request)
    {
        $userData = $this->getUserLogin();
        if (empty($request->name)) {
            return $this->genericResponse(false, 'Warehouse name is required', 400, null, 'createWarehouse', $request);
        }

        $hasDefault = Warehouse::where('branch_id', $userData->branch_id)->where('is_default', true)->exists();

        $wh = Warehouse::create([
            'name' => $request->name,
            'code' => $request->code,
            'branch_id' => $userData->branch_id,
            'institution_id' => $userData->institution_id,
            'manager_id' => $request->manager_id ?: null,
            'capacity' => $request->capacity !== null && $request->capacity !== '' ? (float) $request->capacity : null,
            'is_default' => $hasDefault ? (bool) $request->is_default : true,
            'address' => $request->address,
            'status' => 'Active',
            'created_by' => $userData->id,
            'created_on' => now(),
        ]);

        // only one default per branch
        if ($wh->is_default) {
            Warehouse::where('branch_id', $userData->branch_id)->where('id', '!=', $wh->id)
                ->update(['is_default' => false]);
        }

        return $this->genericResponse(true, 'Warehouse created successfully', 201, $wh, 'createWarehouse', $request);
    }

    public function updateWarehouse(Request $request)
    {
        $userData = $this->getUserLogin();
        $wh = Warehouse::where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!$wh) {
            return $this->genericResponse(false, 'Warehouse not found', 400, null, 'updateWarehouse', $request);
        }
        $wh->name = $request->name ?? $wh->name;
        $wh->code = $request->code ?? $wh->code;
        $wh->manager_id = $request->manager_id ?: null;
        $wh->capacity = $request->capacity !== null && $request->capacity !== '' ? (float) $request->capacity : null;
        $wh->address = $request->address ?? $wh->address;
        if ($request->has('status') && $request->status) {
            $wh->status = $request->status;
        }
        if ($request->is_default) {
            Warehouse::where('branch_id', $wh->branch_id)->update(['is_default' => false]);
            $wh->is_default = true;
        }
        $wh->updated_by = $userData->id;
        $wh->updated_on = now();
        $wh->save();

        return $this->genericResponse(true, 'Warehouse updated successfully', 200, $wh, 'updateWarehouse', $request);
    }

    /**
     * List the branch's warehouses with manager name, location count and a
     * computed utilisation (used base units / capacity).
     */
    public function getWarehouses(Request $request)
    {
        $userData = $this->getUserLogin();
        $warehouses = Warehouse::with(['manager', 'branch'])
            ->where(['institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id])
            ->orderBy('id', 'asc')->get();

        $warehouses->transform(function ($w) {
            $used = (float) WarehouseStock::where('warehouse_id', $w->id)->sum('quantity');
            $w->used_quantity = $used;
            $w->location_count = WarehouseLocation::where('warehouse_id', $w->id)->count();
            $w->utilization = ($w->capacity && $w->capacity > 0) ? round($used / $w->capacity * 100, 1) : null;
            $w->manager_name = $w->manager ? trim(($w->manager->first_name ?? '') . ' ' . ($w->manager->last_name ?? '')) : null;
            $w->branch_name = $w->branch->name ?? null;
            return $w;
        });

        return $this->genericResponse(true, 'Warehouses fetched successfully', 200, $warehouses, 'getWarehouses', $request);
    }

    /**
     * Add a rack / shelf / bin to a warehouse. parent_id nests it under another
     * location (a SHELF under a RACK, a BIN under a SHELF).
     */
    public function createLocation(Request $request)
    {
        $userData = $this->getUserLogin();
        $type = strtoupper($request->type ?? '');
        if (empty($request->warehouse_id) || empty($request->name) || !in_array($type, ['RACK', 'SHELF', 'BIN'])) {
            return $this->genericResponse(false, 'warehouse_id, name and a valid type (RACK/SHELF/BIN) are required', 400, null, 'createLocation', $request);
        }
        $wh = Warehouse::where(['id' => $request->warehouse_id, 'institution_id' => $userData->institution_id])->first();
        if (!$wh) {
            return $this->genericResponse(false, 'Warehouse not found', 400, null, 'createLocation', $request);
        }

        $loc = WarehouseLocation::create([
            'warehouse_id' => $wh->id,
            'parent_id' => $request->parent_id ?: null,
            'type' => $type,
            'name' => $request->name,
            'code' => $request->code,
            'capacity' => $request->capacity !== null && $request->capacity !== '' ? (float) $request->capacity : null,
            'institution_id' => $userData->institution_id,
            'branch_id' => $wh->branch_id,
            'status' => 'Active',
            'created_by' => $userData->id,
            'created_on' => now(),
        ]);

        return $this->genericResponse(true, 'Location added successfully', 201, $loc, 'createLocation', $request);
    }

    /**
     * Return a warehouse's locations as a nested tree (RACK > SHELF > BIN).
     */
    public function getLocations(Request $request)
    {
        $userData = $this->getUserLogin();
        $all = WarehouseLocation::where([
            'warehouse_id' => $request->warehouse_id,
            'institution_id' => $userData->institution_id,
        ])->where('status', 'Active')->orderBy('id', 'asc')->get();

        $byParent = $all->groupBy(fn ($l) => $l->parent_id ?? 0);
        $build = function ($parentId) use (&$build, $byParent) {
            return $byParent->get($parentId ?: 0, collect())->map(function ($node) use (&$build) {
                $node->children = $build($node->id);
                return $node;
            })->values();
        };
        $tree = $build(0);

        return $this->genericResponse(true, 'Locations fetched successfully', 200, ['tree' => $tree, 'flat' => $all], 'getLocations', $request);
    }

    /**
     * The stock sub-ledger for one warehouse (product + bin + quantity).
     */
    public function getWarehouseStock(Request $request)
    {
        $userData = $this->getUserLogin();
        $rows = DB::table('warehouse_stocks as ws')
            ->join('products as p', 'p.id', '=', 'ws.product_id')
            ->leftJoin('warehouse_locations as l', 'l.id', '=', 'ws.location_id')
            ->where('ws.warehouse_id', $request->warehouse_id)
            ->where('ws.institution_id', $userData->institution_id)
            ->select('ws.*', 'p.name as product_name', 'p.ref_no as product_ref', 'l.name as location_name', 'l.type as location_type')
            ->orderByDesc('ws.quantity')->get();

        $total = (float) $rows->sum('quantity');
        return $this->genericResponse(true, 'Warehouse stock fetched successfully', 200, ['items' => $rows, 'total' => $total], 'getWarehouseStock', $request);
    }

    /**
     * Move stock between two warehouses of the same branch. Adjusts both ledger
     * rows and records the transfer. Never touches branch stock or the GL.
     */
    public function createTransfer(Request $request)
    {
        $userData = $this->getUserLogin();
        $qty = (float) $request->quantity;
        if (empty($request->product_id) || empty($request->from_warehouse_id) || empty($request->to_warehouse_id) || $qty <= 0) {
            return $this->genericResponse(false, 'product, source, destination and a positive quantity are required', 400, null, 'createTransfer', $request);
        }
        if ($request->from_warehouse_id == $request->to_warehouse_id) {
            return $this->genericResponse(false, 'Source and destination warehouses must differ', 400, null, 'createTransfer', $request);
        }

        $from = Warehouse::where(['id' => $request->from_warehouse_id, 'institution_id' => $userData->institution_id])->first();
        $to = Warehouse::where(['id' => $request->to_warehouse_id, 'institution_id' => $userData->institution_id])->first();
        if (!$from || !$to) {
            return $this->genericResponse(false, 'Warehouse not found', 400, null, 'createTransfer', $request);
        }
        if ($from->branch_id != $to->branch_id) {
            return $this->genericResponse(false, 'Transfers are only allowed between warehouses of the same branch', 400, null, 'createTransfer', $request);
        }

        return $this->performMove(
            $userData,
            $request->product_id,
            $from,
            $request->from_location_id ?: null,
            $to,
            $request->to_location_id ?: null,
            $qty,
            $request->reason,
            $request->transfer_date,
            'Stock transferred successfully',
            $request
        );
    }

    /**
     * Put away stock within a single warehouse: move quantity from the unassigned
     * pool (or another location) into a specific bin/shelf/rack. Same warehouse,
     * different location.
     */
    public function putAway(Request $request)
    {
        $userData = $this->getUserLogin();
        $qty = (float) $request->quantity;
        if (empty($request->warehouse_id) || empty($request->product_id) || empty($request->to_location_id) || $qty <= 0) {
            return $this->genericResponse(false, 'warehouse, product, destination location and a positive quantity are required', 400, null, 'putAway', $request);
        }
        if (($request->from_location_id ?: null) == ($request->to_location_id ?: null)) {
            return $this->genericResponse(false, 'Source and destination locations must differ', 400, null, 'putAway', $request);
        }

        $wh = Warehouse::where(['id' => $request->warehouse_id, 'institution_id' => $userData->institution_id])->first();
        if (!$wh) {
            return $this->genericResponse(false, 'Warehouse not found', 400, null, 'putAway', $request);
        }
        $dest = WarehouseLocation::where(['id' => $request->to_location_id, 'warehouse_id' => $wh->id])->first();
        if (!$dest) {
            return $this->genericResponse(false, 'Destination location does not belong to this warehouse', 400, null, 'putAway', $request);
        }

        return $this->performMove(
            $userData,
            $request->product_id,
            $wh,
            $request->from_location_id ?: null,
            $wh,
            $request->to_location_id,
            $qty,
            $request->reason ?? 'Put away',
            $request->transfer_date,
            'Stock put away successfully',
            $request
        );
    }

    /**
     * Shared mover: decrement the source ledger row (warehouse + product + location)
     * and increment the destination row, then record a warehouse_transfers entry.
     * location ids may be null (the unassigned pool). Never touches branch stock/GL.
     */
    private function performMove($userData, $productId, $from, $fromLocId, $to, $toLocId, $qty, $reason, $date, $successMsg, $request)
    {
        $source = WarehouseStock::where(['warehouse_id' => $from->id, 'product_id' => $productId])
            ->where(function ($q) use ($fromLocId) {
                $fromLocId ? $q->where('location_id', $fromLocId) : $q->whereNull('location_id');
            })->first();
        if (!$source || (float) $source->quantity < $qty) {
            return $this->genericResponse(false, 'Not enough stock at the source (' . (float) ($source->quantity ?? 0) . ' available)', 400, null, 'performMove', $request);
        }

        DB::beginTransaction();
        try {
            $source->quantity = (float) $source->quantity - $qty;
            $source->updated_by = $userData->id;
            $source->updated_on = now();
            $source->save();

            $dest = WarehouseStock::where(['warehouse_id' => $to->id, 'product_id' => $productId])
                ->where(function ($q) use ($toLocId) {
                    $toLocId ? $q->where('location_id', $toLocId) : $q->whereNull('location_id');
                })->first();
            if (!$dest) {
                $dest = WarehouseStock::create([
                    'warehouse_id' => $to->id,
                    'location_id' => $toLocId ?: null,
                    'product_id' => $productId,
                    'quantity' => 0,
                    'institution_id' => $userData->institution_id,
                    'branch_id' => $to->branch_id,
                    'status' => 'Active',
                    'created_by' => $userData->id,
                    'created_on' => now(),
                ]);
            }
            $dest->quantity = (float) $dest->quantity + $qty;
            $dest->updated_on = now();
            $dest->save();

            $transfer = WarehouseTransfer::create([
                'ref_no' => 'WT-' . strtoupper(substr(str_replace('-', '', $this->generateUuid()), 0, 8)),
                'product_id' => $productId,
                'from_warehouse_id' => $from->id,
                'to_warehouse_id' => $to->id,
                'from_location_id' => $fromLocId ?: null,
                'to_location_id' => $toLocId ?: null,
                'quantity' => $qty,
                'transfer_date' => $date ?? now(),
                'reason' => $reason,
                'transferred_by' => $userData->id,
                'status' => 'Active',
                'institution_id' => $userData->institution_id,
                'branch_id' => $from->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ]);

            DB::commit();
            return $this->genericResponse(true, $successMsg, 201, $transfer, 'performMove', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'performMove', $request);
        }
    }

    public function getTransfers(Request $request)
    {
        $userData = $this->getUserLogin();
        $q = DB::table('warehouse_transfers as t')
            ->join('products as p', 'p.id', '=', 't.product_id')
            ->leftJoin('warehouses as fw', 'fw.id', '=', 't.from_warehouse_id')
            ->leftJoin('warehouses as tw', 'tw.id', '=', 't.to_warehouse_id')
            ->where('t.institution_id', $userData->institution_id)
            ->where('t.branch_id', $userData->branch_id)
            ->select('t.*', 'p.name as product_name', 'fw.name as from_warehouse', 'tw.name as to_warehouse');
        if (!empty($request->product_id)) {
            $q->where('t.product_id', $request->product_id);
        }
        $transfers = $q->orderBy('t.id', 'desc')->get();
        return $this->genericResponse(true, 'Warehouse transfers fetched successfully', 200, $transfers, 'getTransfers', $request);
    }
}
