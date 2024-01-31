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
        Schema::create('saving_account_products', function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->text("description")->nullable();
            $table->double('balance', 8, 2)->default(0);
            $table->double('min_balance', 8, 2)->default(0);
            $table->boolean("withdraw_allowed")->default(true);
            $table->boolean("overdraw_allowed")->default(false);
            $table->boolean("is_default")->default(false);
            $table->string("currency")->default("UGX");
            $table->unsignedBigInteger("institution_id");
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger("user_id");
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
        Schema::dropIfExists('saving_account_products');
    }
};
