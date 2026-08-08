<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Manual stock adjustments (write-on/write-off). An IN adjustment receives a
     * cost layer (Dr Inventory / Cr Stock Adjustment); an OUT adjustment issues
     * stock by the valuation method (Dr Stock Adjustment / Cr Inventory).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->nullable();
            $table->string('type');   // IN | OUT
            $table->string('reason')->nullable();
            $table->double('total_cost')->default(0);
            $table->string('tran_id')->nullable();
            $table->string('status')->default('Active');
            $table->timestamp('adjustment_date')->nullable();
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_adjustment_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('stock_adjustment_id');
            $table->unsignedBigInteger('product_id');
            $table->double('quantity');
            $table->double('unit_cost')->default(0);
            $table->double('total_cost')->default(0);
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
            $table->timestamp('created_on')->nullable();
            $table->timestamps();

            $table->index('stock_adjustment_id');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
    }
};
