<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("invoice_id");
            $table->unsignedBigInteger("product_id");
            $table->double('qty', 8, 2);
            $table->string("status");
            $table->double('selling_price', 8, 2);
            $table->unsignedBigInteger("institution_id");
            $table->unsignedBigInteger("measurement_unit_id")->nullable();
            $table->unsignedBigInteger("branch_id");
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
