<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * History of stock/value movements on a production order: ISSUE (materials into
     * WIP), COMPLETE (finished goods out of WIP), SCRAP (WIP written off).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_order_id');
            $table->string('type'); // ISSUE | COMPLETE | SCRAP
            $table->unsignedBigInteger('product_id')->nullable();
            $table->double('quantity')->default(0);
            $table->double('cost')->default(0);
            $table->string('tran_id')->nullable();
            $table->string('ref_no')->nullable();
            $table->text('note')->nullable();
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamps();

            $table->index('production_order_id');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_movements');
    }
};
