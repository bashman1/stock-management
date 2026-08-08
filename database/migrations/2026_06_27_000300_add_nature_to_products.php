<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cache the behaviour on the product itself (denormalised from its ProductType)
     * so the sale/stock paths don't need a join per item:
     *  - nature: STOCK | NON_STOCK | SERVICE | DIGITAL | BUNDLE (default STOCK)
     *  - tracks_stock: whether quantity is decremented + the out-of-stock check runs
     * Defaults keep every existing product behaving exactly as before.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'nature')) {
                $table->string('nature')->default('STOCK')->after('type_id');
            }
            if (!Schema::hasColumn('products', 'tracks_stock')) {
                $table->boolean('tracks_stock')->default(true)->after('nature');
            }
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'nature')) {
                $table->dropColumn('nature');
            }
            if (Schema::hasColumn('products', 'tracks_stock')) {
                $table->dropColumn('tracks_stock');
            }
        });
    }
};
