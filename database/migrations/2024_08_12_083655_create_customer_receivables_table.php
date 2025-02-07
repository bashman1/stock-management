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
        Schema::create('customer_receivables', function (Blueprint $table) {
            $table->id();
            $table->string("ref_no");
            $table->string("receipt_no");
            $table->string("tran_id");
            $table->unsignedBigInteger("customer_id")->nullable();
            $table->double('amount', 8, 2)->default(0);
            $table->double('amount_paid', 8, 2)->default(0);
            $table->string('status');
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
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
        Schema::dropIfExists('customer_receivables');
    }
};
