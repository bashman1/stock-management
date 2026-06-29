<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Seed one OPENING cost layer for every existing stock row that holds quantity,
     * carrying its current purchase_price, so all on-hand inventory is costed and
     * consumable by the valuation engine. Also default each product's standard_cost
     * to its current purchase_price (valuation_method already defaults to WAVG).
     * After this: SUM(remaining_quantity) == stocks.quantity for every product.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        DB::statement("SELECT setval(pg_get_serial_sequence('inventory_cost_layers', 'id'), COALESCE((SELECT MAX(id) FROM inventory_cost_layers), 1))");

        $stocks = DB::table('stocks')->where('quantity', '>', 0)->get();
        foreach ($stocks as $s) {
            $exists = DB::table('inventory_cost_layers')
                ->where('product_id', $s->product_id)->where('branch_id', $s->branch_id)
                ->where('source_type', 'OPENING')->exists();
            if ($exists) {
                continue;
            }
            DB::table('inventory_cost_layers')->insert([
                'product_id' => $s->product_id,
                'branch_id' => $s->branch_id,
                'institution_id' => $s->institution_id,
                'source_type' => 'OPENING',
                'source_ref' => 'BACKFILL',
                'receipt_date' => $s->stock_date ?? $now,
                'quantity' => (float) $s->quantity,
                'remaining_quantity' => (float) $s->quantity,
                'unit_cost' => (float) ($s->purchase_price ?? 0),
                'status' => 'Active',
                'created_on' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        // seed standard_cost from purchase_price where it is still zero
        DB::statement('UPDATE products p SET standard_cost = s.purchase_price
            FROM stocks s WHERE s.product_id = p.id AND (p.standard_cost IS NULL OR p.standard_cost = 0)');
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        DB::table('inventory_cost_layers')->where('source_ref', 'BACKFILL')->delete();
    }
};
