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
        Schema::create('landload_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('building_id');
            $table->decimal('amount', 15, 2);
            $table->string('payment_type')->default('RENT');
            $table->date('payment_date')->default(now());
            $table->string('payment_method')->default('CASH');
            $table->text('description')->nullable();
            $table->string("status")->default('Active');
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
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
        Schema::dropIfExists('landload_payments');
    }
};
