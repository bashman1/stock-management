<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The materials that make up one BOM batch. `quantity` is the base-unit amount
     * of the material needed per `output_quantity` of the finished good.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bom_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bom_id');
            $table->unsignedBigInteger('material_product_id');
            $table->double('quantity')->default(0);
            $table->unsignedBigInteger('measurement_unit_id')->nullable();
            $table->double('scrap_percent')->default(0);
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();

            $table->index('bom_id');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bom_items');
    }
};
