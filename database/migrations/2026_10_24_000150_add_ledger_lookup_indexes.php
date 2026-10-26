<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The ledger tables had only primary keys. Reversals look rows up by tran_id,
     * reports and the accounting health scan aggregate by acct_no, and a sale's
     * transaction is found by ext_ref (the order ref_no).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gl_histories', function (Blueprint $table) {
            $table->index('acct_no');
            $table->index('tran_id');
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->index('tran_id');
            $table->index('ext_ref');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['branch_id', 'status']);
        });
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('gl_histories', function (Blueprint $table) {
            $table->dropIndex(['acct_no']);
            $table->dropIndex(['tran_id']);
        });
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['tran_id']);
            $table->dropIndex(['ext_ref']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['branch_id', 'status']);
        });
    }
};
