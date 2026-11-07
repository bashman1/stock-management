<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sale lines from before 2026_06_24_000200 never captured a purchase_price (it
     * defaulted to 0 with base_qty NULL), so reports costed them at the CURRENT
     * stocks.purchase_price and every price change re-costed old sales. Freeze a cost
     * on each such line from the stock history as it stood on the sale date:
     *   1. the latest plausible restock cost on or before the sale date, same branch
     *   2. else the earliest plausible restock cost for that product/branch
     *   3. else the current stock cost (what reports showed until now)
     * stock_histories holds base-unit prices, so scale by the sold unit's
     * conversion_factor; base_qty is filled too so the line is no longer legacy.
     *
     * @return void
     */
    public function up()
    {
        $this->backfill('order_items', 'orders', 'COALESCE(Q.tran_date, L.created_at)');
        $this->backfill('invoice_items', 'invoices', 'COALESCE(Q.invoice_date, L.created_at)');
    }

    private function backfill(string $lines, string $headers, string $saleDate): void
    {
        $fk = $lines === 'order_items' ? 'order_id' : 'invoice_id';
        // skip restocks keyed in as a pack/total cost (over 3x the line's selling
        // price, the same test as AccountingHealthController); they cost a line at
        // hundreds of times its price
        $historyCost = "SELECT H.purchase_price FROM stock_histories H
            WHERE H.product_id = L.product_id AND H.branch_id = L.branch_id AND H.purchase_price > 0
            AND (COALESCE(L.selling_price, 0) <= 0 OR H.purchase_price * C.factor <= 3 * L.selling_price)";
        $histDate = "COALESCE(H.stock_date, H.created_on, H.created_at)";

        DB::statement("UPDATE $lines L SET
            purchase_price = ROUND(CAST(COALESCE(
                ($historyCost AND $histDate <= (SELECT $saleDate FROM $headers Q WHERE Q.id = L.$fk)
                    ORDER BY $histDate DESC, H.id DESC LIMIT 1),
                ($historyCost ORDER BY $histDate ASC, H.id ASC LIMIT 1),
                (SELECT S.purchase_price FROM stocks S WHERE S.product_id = L.product_id AND S.branch_id = L.branch_id LIMIT 1),
                0) * C.factor AS NUMERIC), 2),
            base_qty = L.qty * C.factor
            FROM $lines L2
            CROSS JOIN LATERAL (SELECT COALESCE((SELECT PX.conversion_factor FROM product_units PX
                WHERE PX.product_id = L2.product_id AND PX.measurement_unit_id = L2.measurement_unit_id AND PX.status = 'Active'
                ORDER BY (PX.branch_id = L2.branch_id) DESC NULLS LAST, PX.id LIMIT 1), 1) AS factor) C
            WHERE L2.id = L.id AND L.base_qty IS NULL AND COALESCE(L.purchase_price, 0) = 0");
    }

    /**
     * The original costs are not kept, and restoring the zeros would bring back
     * the re-costing bug, so this is not reversed.
     *
     * @return void
     */
    public function down()
    {
    }
};
