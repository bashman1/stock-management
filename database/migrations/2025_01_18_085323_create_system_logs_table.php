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
        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->string("action")->nullable();
            $table->string("ip")->nullable();
            $table->string("http_code")->nullable();
            $table->string("return_message")->nullable();
            $table->boolean("return_status")->nullable();
            $table->json("request")->nullable();
            $table->json("response")->nullable();
            $table->text("status")->default("Success");
            $table->unsignedBigInteger('user_id')->nullable();
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
        Schema::dropIfExists('system_logs');
    }
};
