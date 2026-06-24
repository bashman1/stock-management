<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Backfill product_units from the legacy primary + optional secondary unit model.
     *
     * Legacy semantics: products.measurement_unit_id was the PRIMARY (base) unit, and
     * secondary_weight = "number of secondary components contained in one primary unit".
     * The new conversion_factor = "base units contained in ONE of this unit", which is the
     * INVERSE for the secondary row: conversion_factor = 1 / secondary_weight.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        $products = DB::table('products')->get();

        foreach ($products as $product) {
            if (empty($product->measurement_unit_id)) {
                continue;
            }

            // skip products that already have units (idempotent re-run safety)
            $exists = DB::table('product_units')->where('product_id', $product->id)->exists();
            if ($exists) {
                continue;
            }

            $stock = DB::table('stocks')->where('product_id', $product->id)->first();
            $basePurchase = $stock->purchase_price ?? 0;
            $baseSelling  = $stock->selling_price ?? 0;
            $branchId     = $stock->branch_id ?? null;

            // base row (the legacy primary unit)
            DB::table('product_units')->insert([
                'product_id'          => $product->id,
                'measurement_unit_id' => $product->measurement_unit_id,
                'conversion_factor'   => 1,
                'purchase_price'      => $basePurchase,
                'selling_price'       => $baseSelling,
                'is_base'             => true,
                'institution_id'      => $product->institution_id,
                'branch_id'           => $branchId,
                'status'              => 'Active',
                'created_by'          => $product->created_by ?? null,
                'created_on'          => $now,
                'created_at'          => $now,
                'updated_at'          => $now,
            ]);

            // legacy secondary unit, if configured
            $secWeight = isset($product->secondary_weight) ? (float) $product->secondary_weight : 0;
            if (!empty($product->secondary_measurement_unit_id) && $secWeight > 0) {
                DB::table('product_units')->insert([
                    'product_id'          => $product->id,
                    'measurement_unit_id' => $product->secondary_measurement_unit_id,
                    // INVERSE of the legacy secondary_weight (see note above)
                    'conversion_factor'   => 1 / $secWeight,
                    'purchase_price'      => $basePurchase,
                    'selling_price'       => $product->secondary_price ?? 0,
                    'is_base'             => false,
                    'institution_id'      => $product->institution_id,
                    'branch_id'           => $branchId,
                    'status'              => 'Active',
                    'created_by'          => $product->created_by ?? null,
                    'created_on'          => $now,
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // data-only migration; the product_units table is dropped by its own migration
        DB::table('product_units')->truncate();
    }
};
