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
        Schema::create('products', function (Blueprint $table) {
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
            $table->string("status")->nullable();
            $table->string("ref_no")->nullable();
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
        Schema::dropIfExists('products');
    }
};
