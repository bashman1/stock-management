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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('building_id');
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('member_id');
            $table->double('duration', 8, 2)->default(0);
            $table->double('amount_paid', 8, 2)->default(0);
            $table->string('status')->default('Active');
            $table->string('ref_no')->nullable();
            $table->timestamp('rental_date')->nullable();
            $table->timestamp('due_date')->nullable();
            $table->timestamp('paid_date')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->string('payment_status')->default('UNPAID');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
