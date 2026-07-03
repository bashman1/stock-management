<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Reusable payment-terms master list (e.g. COD, Net 30). Suppliers reference a
     * term via suppliers.payment_term_id. Seeds a few defaults per institution.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_terms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('days')->default(0);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamp('updated_on')->nullable();
            $table->timestamps();

            $table->index('institution_id');
        });

        $now = Carbon::now();
        $defaults = [
            ['name' => 'Cash on Delivery', 'days' => 0, 'description' => 'Payment on delivery'],
            ['name' => 'Net 15', 'days' => 15, 'description' => 'Due within 15 days'],
            ['name' => 'Net 30', 'days' => 30, 'description' => 'Due within 30 days'],
            ['name' => 'Net 60', 'days' => 60, 'description' => 'Due within 60 days'],
        ];
        $instIds = DB::table('suppliers')->distinct()->pluck('institution_id')
            ->merge(DB::table('institutions')->pluck('id'))->filter()->unique();
        foreach ($instIds as $instId) {
            foreach ($defaults as $d) {
                $exists = DB::table('payment_terms')->where('institution_id', $instId)->where('name', $d['name'])->exists();
                if (!$exists) {
                    DB::table('payment_terms')->insert(array_merge($d, [
                        'institution_id' => $instId, 'status' => 'Active',
                        'created_on' => $now, 'created_at' => $now, 'updated_at' => $now,
                    ]));
                }
            }
        }
    }

    /**
     * Reverse the migration.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_terms');
    }
};
