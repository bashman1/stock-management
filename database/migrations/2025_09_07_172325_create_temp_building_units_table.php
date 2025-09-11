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
        Schema::create('temp_building_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('building_id');
            $table->string('unit_type')->nullable();
            $table->string('unit_no')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('batch_id')->nullable();
            $table->text('description')->nullable();
            $table->double('rent', 8, 2)->default(0);
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
        Schema::dropIfExists('temp_building_units');
    }
};
