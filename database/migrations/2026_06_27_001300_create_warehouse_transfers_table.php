<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A record of moving stock between two warehouses of the same branch.
     * Inventory-to-inventory: it adjusts both warehouse_stocks rows but never the
     * branch stocks total and never the GL.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('ref_no')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('from_warehouse_id');
            $table->unsignedBigInteger('to_warehouse_id');
            $table->unsignedBigInteger('from_location_id')->nullable();
            $table->unsignedBigInteger('to_location_id')->nullable();
            $table->double('quantity'); // base units
            $table->timestamp('transfer_date')->nullable();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('transferred_by')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();

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
        Schema::dropIfExists('warehouse_transfers');
    }
};
