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
        Schema::create('printer_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('paper')->default('Thermal');
            $table->string('printer')->default('POS-80');
            $table->string('size')->default('80mm');
            $table->text('address')->nullable();
            $table->string('p_o_box')->nullable();
            $table->text('thanks_message')->nullable();
            $table->text('powered_message')->nullable();
            $table->text('phone_number')->nullable();
            $table->text('email')->nullable();
            $table->boolean('auto')->default(false);
            $table->text('footer')->nullable();
            $table->string('currency')->default('UGX');
            $table->text('description')->nullable();
            $table->string("status")->default('Active');
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
        Schema::dropIfExists('printer_configs');
    }
};
