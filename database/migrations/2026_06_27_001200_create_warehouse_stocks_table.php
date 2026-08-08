<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-warehouse stock sub-ledger: how much of each product physically sits in
     * each warehouse (optionally a bin). Quantity is in base units, mirroring the
     * branch stocks table. SUM over a branch's warehouses should track that
     * branch's stocks.quantity (kept in sync by the sale/create/restock hooks).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_stocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('location_id')->nullable(); // a BIN, optional
            $table->unsignedBigInteger('product_id');
            $table->double('quantity')->default(0); // base units
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();

            $table->index(['warehouse_id', 'product_id']);
            $table->index('product_id');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_stocks');
    }
};
