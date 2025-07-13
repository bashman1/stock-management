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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('duration_id')->nullable();
            $table->string('benefit_type')->default('Percentage'); // e.g., Percentage, Fixed Amount
            $table->decimal('benefit', 15, 2)->default(0);
            $table->decimal('monitory', 15, 2)->default(0);
            $table->timestamp('date')->default(now());
            $table->string('status')->default('Active');
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
        Schema::dropIfExists('projects');
    }
};
