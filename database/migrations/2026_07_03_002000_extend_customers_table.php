<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Give customers the management fields: group, credit limit, a default
     * discount %, payment term (reused from payment_terms) and tax id.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            foreach ([
                'customer_group_id' => fn () => $table->unsignedBigInteger('customer_group_id')->nullable(),
                'credit_limit' => fn () => $table->double('credit_limit')->default(0),
                'discount_percent' => fn () => $table->double('discount_percent')->nullable(),
                'payment_term_id' => fn () => $table->unsignedBigInteger('payment_term_id')->nullable(),
                'tin' => fn () => $table->string('tin')->nullable(),
            ] as $col => $add) {
                if (!Schema::hasColumn('customers', $col)) {
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
        Schema::table('customers', function (Blueprint $table) {
            foreach (['customer_group_id', 'credit_limit', 'discount_percent', 'payment_term_id', 'tin'] as $col) {
                if (Schema::hasColumn('customers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
