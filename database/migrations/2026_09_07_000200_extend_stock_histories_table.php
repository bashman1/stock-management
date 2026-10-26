<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Label what each stock_histories row actually is, so the stock statement can
     * tell a goods receipt from a correction, and so the "total purchased" sums
     * (supplier/manufacturer profiles, analytics) can keep counting receipts only.
     *
     * OPENING    - stock a product was created with
     * RESTOCK    - goods received via restock()
     * ADJUSTMENT - a correction from editing a product's quantity; may be negative
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_histories', function (Blueprint $table) {
            foreach ([
                'source_type' => fn () => $table->string('source_type')->nullable(),
            ] as $col => $add) {
                if (!Schema::hasColumn('stock_histories', $col)) {
                    $add();
                }
            }
        });

        // Every existing row is a receipt (adjustments were never recorded — editing
        // a product overwrote the latest row instead of appending). The oldest row
        // per product+stock is that product's opening stock; the rest are restocks.
        DB::statement("UPDATE stock_histories SET source_type = 'RESTOCK' WHERE source_type IS NULL");
        DB::statement("UPDATE stock_histories SET source_type = 'OPENING' WHERE id IN (
            SELECT DISTINCT ON (product_id, stock_id) id
            FROM stock_histories
            ORDER BY product_id, stock_id, created_at ASC, id ASC
        )");
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_histories', function (Blueprint $table) {
            foreach (['source_type'] as $col) {
                if (Schema::hasColumn('stock_histories', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
