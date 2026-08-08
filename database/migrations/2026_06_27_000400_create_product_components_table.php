<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Components that make up a BUNDLE product. When the bundle sells, each
     * component product's stock is decremented by (quantity x bundles sold) and
     * its cost feeds the bundle's COGS. The bundle itself is not inventoried.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bundle_product_id');
            $table->unsignedBigInteger('component_product_id');
            $table->double('quantity', 12, 4)->default(1); // base units of the component per one bundle
            $table->unsignedBigInteger('measurement_unit_id')->nullable();
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();

            $table->index('bundle_product_id');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_components');
    }
};
