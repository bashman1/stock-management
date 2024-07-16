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
        Schema::create('temp_products', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("product_no")->nullable();
            $table->unsignedBigInteger("type_id")->nullable();
            $table->unsignedBigInteger("category_id")->nullable();
            $table->unsignedBigInteger("sub_category_id")->nullable();
            $table->unsignedBigInteger("manufacturer_id")->nullable();
            $table->unsignedBigInteger("supplier_id")->nullable();
            $table->unsignedBigInteger("measurement_unit_id")->nullable();
            $table->unsignedBigInteger("gauge_id")->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger("institution_id");
            $table->unsignedBigInteger("user_id");
            $table->double('purchase_price', 8, 2)->default(0)->nullable();
            $table->double('selling_price', 8, 2)->default(0);
            $table->double('discount', 8, 2)->default(0)->nullable();
            $table->double("quantity", 8, 2);
            $table->double("min_quantity", 8, 2)->nullable();
            $table->double("max_quantity", 8, 2)->nullable();
            $table->timestamp('stock_date')->nullable();
            $table->timestamp('manufactured_date')->nullable();
            $table->timestamp('expiry_date')->nullable();

            $table->string('tax_config')->nullable();
            $table->string("ref_no")->nullable();
            $table->string("status")->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('temp_products');
    }
};
