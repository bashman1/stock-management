<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perpetual inventory cost layers. Each stock receipt (opening, purchase,
     * production, adjustment) creates a layer with its unit cost; issues consume
     * remaining_quantity from layers per the product's valuation method.
     * Invariant: SUM(remaining_quantity) per (product, branch) == stocks.quantity.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_cost_layers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('institution_id');
            $table->string('source_type')->default('PURCHASE'); // OPENING|PURCHASE|PRODUCTION|ADJUSTMENT|RETURN
            $table->string('source_ref')->nullable();
            $table->timestamp('receipt_date')->nullable();
            $table->double('quantity');            // original received qty (base units)
            $table->double('remaining_quantity');  // not yet issued
            $table->double('unit_cost');           // carrying cost per base unit
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'branch_id', 'status']);
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_cost_layers');
    }
};
