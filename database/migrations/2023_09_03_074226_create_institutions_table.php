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
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ref_no');
            $table->unsignedBigInteger('institution_type_id');
            $table->timestamp('start_date');
            $table->text('address')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('street')->nullable();
            $table->string('tin')->nullable();
            $table->string('p_o_box')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_tax_enabled')->default(true);
            $table->string("status");
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
        Schema::dropIfExists('institutions');
    }
};
