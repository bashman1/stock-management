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
        Schema::create('gl_generate_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('gl_no');
            $table->text('description');
            $table->string('gl_cat_no');
            $table->string('gl_sub_cat_no');
            $table->string('gl_type_no');
            $table->string('acct_type');
            $table->double('const', 8, 2)->default(0)->nullable();
            $table->unsignedBigInteger('current')->default(1)->nullable();
            $table->unsignedBigInteger('step')->default(1)->nullable();
            $table->string('status');
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
        Schema::dropIfExists('gl_generate_accounts');
    }
};
