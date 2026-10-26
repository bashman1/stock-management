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
        Schema::create('institution_licenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('license_package_id');
            $table->unsignedBigInteger('license_package_price_id');
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->decimal('quantity', 10, 2)->default(1);
            $table->decimal('paid', 15, 2)->default(0);
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->text("status")->default("Active");
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
        Schema::dropIfExists('institution_licenses');
    }
};
