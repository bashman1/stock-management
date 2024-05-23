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
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->double('purchase_price', 8, 2)->default(0)->nullable();
            $table->double('selling_price', 8, 2)->default(0);
            $table->double('discount', 8, 2)->default(0)->nullable();
            $table->unsignedBigInteger("product_id");
            $table->unsignedBigInteger("stock_id");
            // $table->unsignedBigInteger("quantity");
            // $table->unsignedBigInteger("min_quantity")->nullable();
            // $table->unsignedBigInteger("max_quantity")->nullable();
            $table->double("quantity", 8, 2);
            $table->double("min_quantity", 8, 2)->nullable();
            $table->double("max_quantity", 8, 2)->nullable();
            $table->unsignedBigInteger("institution_id");
            $table->timestamp('stock_date')->nullable();
            $table->timestamp('manufactured_date')->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("user_id");
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
        Schema::dropIfExists('stock_histories');
    }
};
