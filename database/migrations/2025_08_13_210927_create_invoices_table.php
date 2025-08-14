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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string("name")->nullable();
            $table->string("invoice_type")->default("Proforma");
            $table->string( "ref_no")->nullable();
            $table->string("invoice_no")->nullable();
            // $table->string("invoice_id")->nullable();
            $table->unsignedBigInteger( 'item_count')->nullable();
            $table->double('total', 8, 2)->default(0);
            $table->double('discount', 8, 2)->default(0);
            $table->double('amount_paid', 8, 2)->default(0);
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("customer_id")->nullable();
            $table->timestamp('invoice_date')->nullable();
            $table->string("status")->nullable();
            $table->string("address")->nullable();
            $table->string("payment_status")->default('UNPAID');
            $table->unsignedBigInteger("institution_id");
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
        Schema::dropIfExists('invoices');
    }
};
