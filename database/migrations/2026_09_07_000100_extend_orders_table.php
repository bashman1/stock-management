<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Let a sale carry the customer's physical (hand-written / external) receipt
     * number, so it can be reconciled against our generated receipt_no.
     * Optional free text — duplicates across receipt books are possible, so no
     * unique index.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach ([
                'physical_receipt_no' => fn () => $table->string('physical_receipt_no')->nullable(),
            ] as $col => $add) {
                if (!Schema::hasColumn('orders', $col)) {
                    $add();
                }
            }
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            foreach (['physical_receipt_no'] as $col) {
                if (Schema::hasColumn('orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
