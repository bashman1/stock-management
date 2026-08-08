<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Give ProductType a behaviour discriminator. `nature` drives how a product
     * of this type is stocked and posted to the GL (STOCK | NON_STOCK | SERVICE |
     * DIGITAL | BUNDLE). `revenue_acct_no` is an optional per-type override for the
     * income account credited on sale (else the nature's default account is used).
     *
     * Existing types default to STOCK so current products keep their behaviour.
     * Then seed the five standard types per institution so the create-product
     * dropdown offers each nature.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_types', function (Blueprint $table) {
            if (!Schema::hasColumn('product_types', 'nature')) {
                $table->string('nature')->default('STOCK')->after('description');
            }
            if (!Schema::hasColumn('product_types', 'revenue_acct_no')) {
                $table->string('revenue_acct_no')->nullable()->after('nature');
            }
        });

        $now = Carbon::now();
        $standard = [
            ['name' => 'Stock Item', 'nature' => 'STOCK', 'description' => 'Tracked inventory item'],
            ['name' => 'Non-stock Item', 'nature' => 'NON_STOCK', 'description' => 'Bought and expensed, not inventoried'],
            ['name' => 'Service', 'nature' => 'SERVICE', 'description' => 'Service rendered, no inventory'],
            ['name' => 'Digital Product', 'nature' => 'DIGITAL', 'description' => 'Digital good, no inventory'],
            ['name' => 'Bundle', 'nature' => 'BUNDLE', 'description' => 'Sold as one price, decrements its components'],
        ];

        // seed the standard types for every institution that already has product data
        $instIds = DB::table('products')->distinct()->pluck('institution_id')
            ->merge(DB::table('product_types')->distinct()->pluck('institution_id'))
            ->filter()->unique();

        foreach ($instIds as $instId) {
            $user = DB::table('users')->where('institution_id', $instId)->orderBy('id')->first();
            foreach ($standard as $s) {
                $exists = DB::table('product_types')
                    ->where('institution_id', $instId)
                    ->whereRaw('LOWER(name) = ?', [strtolower($s['name'])])
                    ->exists();
                if (!$exists) {
                    DB::table('product_types')->insert([
                        'name' => $s['name'], 'description' => $s['description'], 'nature' => $s['nature'],
                        'institution_id' => $instId, 'branch_id' => null,
                        'user_id' => $user->id ?? 0, 'status' => 'Active',
                        'created_by' => $user->id ?? null, 'created_on' => $now,
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
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
        DB::table('product_types')->whereIn('name', [
            'Stock Item', 'Non-stock Item', 'Service', 'Digital Product', 'Bundle',
        ])->whereIn('nature', ['STOCK', 'NON_STOCK', 'SERVICE', 'DIGITAL', 'BUNDLE'])->delete();

        Schema::table('product_types', function (Blueprint $table) {
            if (Schema::hasColumn('product_types', 'nature')) {
                $table->dropColumn('nature');
            }
            if (Schema::hasColumn('product_types', 'revenue_acct_no')) {
                $table->dropColumn('revenue_acct_no');
            }
        });
    }
};
