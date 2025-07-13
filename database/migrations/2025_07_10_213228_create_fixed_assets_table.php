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
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('asset_account')->nullable();
            $table->text('expense_account')->nullable();
            $table->unsignedBigInteger('depreciation_rate_id');
            $table->double('purchase_amount', 8, 2)->default(0);
            $table->unsignedBigInteger('payment_method_id');
            $table->unsignedBigInteger('duration_id');
            $table->string('status')->default('Active');
            $table->timestamp('purchase_date')->default(now());
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
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
        Schema::dropIfExists('fixed_assets');
    }
};
