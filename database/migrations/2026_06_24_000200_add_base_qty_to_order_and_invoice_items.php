<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sales lines now store the quantity in the unit the customer chose (qty)
     * plus the equivalent in base units (base_qty) for stock/EFRIS/aggregation,
     * and the purchase_price captured per chosen unit at sale time.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->double('base_qty', 12, 4)->nullable()->after('qty');
            $table->double('purchase_price', 12, 2)->default(0)->nullable()->after('selling_price');
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->double('base_qty', 12, 4)->nullable()->after('qty');
            $table->double('purchase_price', 12, 2)->default(0)->nullable()->after('selling_price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['base_qty', 'purchase_price']);
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropColumn(['base_qty', 'purchase_price']);
        });
    }
};
