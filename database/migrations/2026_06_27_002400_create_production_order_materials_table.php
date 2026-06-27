<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The exploded BOM for a production order: how much of each material the order
     * needs and how much has actually been consumed (at issue time).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_order_materials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->unsignedBigInteger('material_product_id');
            $table->double('quantity_required');
            $table->double('quantity_consumed')->default(0);
            $table->double('unit_cost')->default(0);
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();

            $table->index('production_order_id');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_order_materials');
    }
};
