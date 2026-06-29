<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Costed audit trail of every inventory IN/OUT movement (receipt or issue),
     * recording the quantity, the unit/total cost the valuation engine applied,
     * and the method used. Supports reporting and traceability.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('institution_id');
            $table->string('direction'); // IN | OUT
            $table->double('quantity');
            $table->double('unit_cost')->default(0);
            $table->double('total_cost')->default(0);
            $table->string('method')->nullable();      // FIFO|LIFO|WAVG|STANDARD
            $table->string('source_type')->nullable(); // SALE|PURCHASE|OPENING|PRODUCTION|ADJUSTMENT|RETURN
            $table->string('ref_no')->nullable();
            $table->string('tran_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory_movements');
    }
};
