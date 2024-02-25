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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string("ref_no");
            $table->string("receipt_no");
            $table->string("tran_id");
            $table->unsignedBigInteger('item_count')->nullable();
            $table->double('total', 8, 2)->default(0);
            $table->double('discount', 8, 2)->default(0);
            $table->double('amount_paid', 8, 2)->default(0);
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("customer_id");
            $table->timestamp('tran_date')->nullable();
            $table->string("status")->nullable();
            $table->string("payment_status")->nullable();
            $table->unsignedBigInteger("institution_id");
            $table->unsignedBigInteger("branch_id");
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
        Schema::dropIfExists('orders');
    }
};
