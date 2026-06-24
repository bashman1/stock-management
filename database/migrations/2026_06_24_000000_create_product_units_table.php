<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("product_id");
            $table->unsignedBigInteger("measurement_unit_id");
            // base units contained in ONE of this unit; the base unit row has conversion_factor = 1
            $table->double('conversion_factor', 12, 4)->default(1);
            $table->double('purchase_price', 12, 2)->default(0);
            $table->double('selling_price', 12, 2)->default(0);
            // exactly one row per product has is_base = true (enforced in the controller)
            $table->boolean('is_base')->default(false);
            $table->unsignedBigInteger("institution_id");
            $table->unsignedBigInteger("branch_id")->nullable();
            $table->string("status")->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();
            $table->index(['product_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_units');
    }
};
