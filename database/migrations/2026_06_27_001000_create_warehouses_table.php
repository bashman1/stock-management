<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A warehouse belongs to a branch. Branch-level stock stays the source of
     * truth; warehouses subdivide it physically (see warehouse_stocks). capacity
     * is in base units; utilisation is computed as SUM(ledger qty)/capacity.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('manager_id')->nullable(); // users.id
            $table->double('capacity')->nullable();                // max base units
            $table->boolean('is_default')->default(false);
            $table->text('address')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();

            $table->index(['institution_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('warehouses');
    }
};
