<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Extend manufacturers with the same profile fields as suppliers: address,
     * city, payment term reference, credit limit, cached rating and tax id.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('manufacturers', function (Blueprint $table) {
            foreach ([
                'address' => fn () => $table->text('address')->nullable(),
                'city_id' => fn () => $table->unsignedBigInteger('city_id')->nullable(),
                'payment_term_id' => fn () => $table->unsignedBigInteger('payment_term_id')->nullable(),
                'credit_limit' => fn () => $table->double('credit_limit')->default(0),
                'rating' => fn () => $table->double('rating')->nullable(),
                'tin' => fn () => $table->string('tin')->nullable(),
            ] as $col => $add) {
                if (!Schema::hasColumn('manufacturers', $col)) {
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
        Schema::table('manufacturers', function (Blueprint $table) {
            foreach (['address', 'city_id', 'payment_term_id', 'credit_limit', 'rating', 'tin'] as $col) {
                if (Schema::hasColumn('manufacturers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
