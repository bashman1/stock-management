<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit trail for the admin Accounting Health repairs: one row per fix applied
     * to a branch, with the affected row ids and before-values in `details` so a
     * repair can be traced (and undone by hand) later.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounting_fix_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institution_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('issue_code');
            $table->text('summary');
            $table->integer('rows_affected')->default(0);
            $table->decimal('amount', 20, 2)->default(0);
            $table->json('details')->nullable();
            $table->string('tran_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamps();
            $table->index(['institution_id', 'created_on']);
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounting_fix_logs');
    }
};
