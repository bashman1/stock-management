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
        Schema::create('manufacturers', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("website")->nullable();
            $table->string("email")->nullable();
            $table->string("phone_number")->nullable();
            $table->unsignedBigInteger("country_id")->nullable();
            $table->unsignedBigInteger("institution_id")->nullable();
            $table->unsignedBigInteger("branch_id")->nullable();
            $table->unsignedBigInteger("user_id")->nullable();
            $table->string("description")->nullable();
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
        Schema::dropIfExists('manufacturers');
    }
};
