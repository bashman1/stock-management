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
        Schema::create('gl_histories', function (Blueprint $table) {
            $table->id();
            $table->string('acct_no');
            $table->string('dr_cr_ind');
            $table->double('tran_amount', 8, 2)->default(0)->nullable();
            $table->string('reversal_flag')->default('N');
            $table->text('description')->nullable();
            $table->timestamp('transaction_date')->nullable();
            $table->string('contra_acct_no')->nullable();
            $table->string('contra_acct_type')->nullable();
            $table->string('tran_type')->nullable();
            $table->string('tran_id')->nullable();
            $table->string('tran_cd')->nullable();
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
        Schema::dropIfExists('gl_histories');
    }
};
