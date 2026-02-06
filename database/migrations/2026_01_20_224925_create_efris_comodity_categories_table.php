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
        Schema::create('efris_comodity_categories', function (Blueprint $table) {
            $table->id();
            $table->string('commodity_category_code')->unique();
            $table->string('commodity_category_level');
            $table->string('commodity_category_name');
            $table->string('enable_status_code');
            $table->string('excisable')->nullable();
            $table->string('is_leaf_node')->nullable();
            $table->string('vat_out_scope')->nullable();
            $table->string('parent_code')->nullable();
            $table->timestamp('source_now_time')->nullable();
            $table->string("status")->default('Active');
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
        Schema::dropIfExists('efris_comodity_categories');
    }
};
