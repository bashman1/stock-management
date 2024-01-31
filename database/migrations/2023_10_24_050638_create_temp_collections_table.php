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
        Schema::create('temp_collections', function (Blueprint $table) {
            $table->id();
            $table->double('amount', 8, 2);
            $table->text("description");
            $table->timestamp("tran_date");
            $table->string("member_number");
            $table->string("tran_cd")->nullable();
            $table->string("tran_indicator")->nullable();
            $table->unsignedBigInteger("member_id");
            $table->unsignedBigInteger("institution_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("user_id");
            $table->string("tran_id");
            $table->string("status");
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('declined_by')->nullable();
            $table->timestamp('approved_on')->nullable();
            $table->timestamp('declined_on')->nullable();
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
        Schema::dropIfExists('temp_collections');
    }
};
