<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-product inventory valuation method (drives how COGS is costed) and the
     * standard cost used when the method is STANDARD. Default WAVG keeps existing
     * products behaving like the previous moving-average cost.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'valuation_method')) {
                $table->string('valuation_method')->default('WAVG')->after('tracks_stock'); // FIFO|LIFO|WAVG|STANDARD
            }
            if (!Schema::hasColumn('products', 'standard_cost')) {
                $table->double('standard_cost')->default(0)->after('valuation_method');
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
            if (Schema::hasColumn('products', 'valuation_method')) {
                $table->dropColumn('valuation_method');
            }
            if (Schema::hasColumn('products', 'standard_cost')) {
                $table->dropColumn('standard_cost');
            }
        });
    }
};
