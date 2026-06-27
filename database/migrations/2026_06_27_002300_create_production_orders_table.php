<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A request to manufacture `quantity` units of `product_id` from `bom_id`.
     * Lifecycle: Draft -> InProgress (materials issued, cost held in wip_cost) ->
     * Completed. wip_cost is the running WIP balance for this order.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('production_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->nullable();
            $table->unsignedBigInteger('bom_id');
            $table->unsignedBigInteger('product_id');         // finished good
            $table->double('quantity');                       // to produce
            $table->double('quantity_completed')->default(0);
            $table->double('quantity_scrapped')->default(0);
            $table->double('wip_cost')->default(0);           // running WIP balance
            $table->string('status')->default('Draft');       // Draft|InProgress|Completed|Cancelled
            $table->timestamp('scheduled_date')->nullable();
            $table->timestamp('started_on')->nullable();
            $table->timestamp('completed_on')->nullable();
            $table->string('tran_id')->nullable();
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_orders');
    }
};
