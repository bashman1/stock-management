<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Give every existing branch a default "Main Warehouse" and seed the
     * warehouse_stocks sub-ledger from the current branch stock, so the ledger
     * starts perfectly consistent with stocks.quantity.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        foreach (['warehouses', 'warehouse_stocks'] as $t) {
            DB::statement("SELECT setval(pg_get_serial_sequence('$t', 'id'), COALESCE((SELECT MAX(id) FROM $t), 1))");
        }

        // one default warehouse per branch
        $branches = DB::table('branches')->get();
        foreach ($branches as $b) {
            $exists = DB::table('warehouses')
                ->where('branch_id', $b->id)->where('is_default', true)->exists();
            if ($exists) {
                continue;
            }
            DB::table('warehouses')->insert([
                'name' => 'Main Warehouse',
                'code' => 'MAIN',
                'branch_id' => $b->id,
                'institution_id' => $b->institution_id,
                'manager_id' => null,
                'capacity' => null,
                'is_default' => true,
                'address' => $b->address ?? null,
                'status' => 'Active',
                'created_on' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        // backfill the ledger from current branch stock
        $defaults = DB::table('warehouses')->where('is_default', true)
            ->get()->keyBy('branch_id');
        $stocks = DB::table('stocks')->get();
        foreach ($stocks as $s) {
            $wh = $defaults->get($s->branch_id);
            if (!$wh) {
                continue;
            }
            $already = DB::table('warehouse_stocks')
                ->where('warehouse_id', $wh->id)
                ->where('product_id', $s->product_id)
                ->whereNull('location_id')
                ->exists();
            if ($already) {
                continue;
            }
            DB::table('warehouse_stocks')->insert([
                'warehouse_id' => $wh->id,
                'location_id' => null,
                'product_id' => $s->product_id,
                'quantity' => (float) $s->quantity,
                'institution_id' => $s->institution_id,
                'branch_id' => $s->branch_id,
                'status' => 'Active',
                'created_on' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        DB::table('warehouse_stocks')->truncate();
        DB::table('warehouses')->where('code', 'MAIN')->where('is_default', true)->delete();
    }
};
