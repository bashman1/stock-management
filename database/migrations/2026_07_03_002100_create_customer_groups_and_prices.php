<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Customer groups (with a default discount %) and per-customer/per-group
     * product price overrides. Effective price at sale = customer override ->
     * group price -> normal selling price.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('discount_percent')->default(0);
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

        Schema::create('customer_prices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('customer_group_id')->nullable();
            $table->unsignedBigInteger('product_id');
            $table->double('price')->default(0);
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('status')->default('Active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_on')->nullable();
            $table->timestamps();
            $table->index('customer_id');
            $table->index('customer_group_id');
            $table->index('product_id');
        });

        // seed a couple of default groups per institution
        $now = Carbon::now();
        $defaults = [
            ['name' => 'Retail', 'discount_percent' => 0, 'description' => 'Walk-in retail customers'],
            ['name' => 'Wholesale', 'discount_percent' => 5, 'description' => 'Bulk / wholesale buyers'],
        ];
        $instIds = DB::table('customers')->distinct()->pluck('institution_id')
            ->merge(DB::table('institutions')->pluck('id'))->filter()->unique();
        foreach ($instIds as $instId) {
            foreach ($defaults as $d) {
                $exists = DB::table('customer_groups')->where('institution_id', $instId)->where('name', $d['name'])->exists();
                if (!$exists) {
                    DB::table('customer_groups')->insert(array_merge($d, [
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
        Schema::dropIfExists('customer_prices');
        Schema::dropIfExists('customer_groups');
    }
};
