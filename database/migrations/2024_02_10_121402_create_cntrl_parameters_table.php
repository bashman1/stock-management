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
        Schema::create('cntrl_parameters', function (Blueprint $table) {
            $table->id();
            $table->string("param_name");
            $table->string("param_cd");
            $table->string("param_value");
            $table->string("status")->default('Active');
            $table->unsignedBigInteger('institution_id');
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
        Schema::dropIfExists('cntrl_parameters');
    }
};
