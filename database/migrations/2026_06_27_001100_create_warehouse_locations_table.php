<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The rack/shelf/bin layout inside a warehouse, modelled as one self-
     * referencing tree: type = RACK | SHELF | BIN, parent_id points at the
     * containing location (a RACK has no parent, a SHELF's parent is a RACK,
     * a BIN's parent is a SHELF). Stock is placed at a BIN (or directly at the
     * warehouse when no bin is given).
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouse_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('type'); // RACK | SHELF | BIN
            $table->string('name');
            $table->string('code')->nullable();
            $table->double('capacity')->nullable();
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id');
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();

            $table->index('warehouse_id');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouse_locations');
    }
};
