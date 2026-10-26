<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A Bill of Materials: the recipe for producing a finished product. One BOM
     * yields `output_quantity` units of `product_id` from its bom_items.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('boms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');        // finished good
            $table->string('name')->nullable();
            $table->integer('version')->default(1);
            $table->double('output_quantity')->default(1);   // units produced per batch
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();

            $table->index('product_id');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('boms');
    }
};
