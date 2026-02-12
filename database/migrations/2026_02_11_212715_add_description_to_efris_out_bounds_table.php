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
        Schema::table('efris_out_bounds', function (Blueprint $table) {
            $table->string('action')->nullable();
            $table->boolean('success')->default(false);
            $table->string('return_code')->nullable();
            $table->string('return_message')->nullable();
            $table->string('execution')->nullable();
            $table->text('description')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('efris_out_bounds', function (Blueprint $table) {
            //
        });
    }
};
