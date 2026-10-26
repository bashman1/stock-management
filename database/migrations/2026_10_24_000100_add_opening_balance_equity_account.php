<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * 3109001 Opening Balance Equity (CAPITAL, param OBE): the credit side of
     * opening stock entered with a new product. Opening stock was credited to Cash
     * in hand, which drove cash negative with no capital behind the inventory.
     * Adds it to the chart template, backfills gl_accounts/gl_balances per
     * institution/branch, and registers the control parameter.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        foreach (['gl_acct_bks', 'gl_accounts', 'gl_balances', 'cntrl_parameters'] as $t) {
            DB::statement("SELECT setval(pg_get_serial_sequence('$t', 'id'), COALESCE((SELECT MAX(id) FROM $t), 1))");
        }

        $a = ['gl_no' => '3109001', 'description' => 'Opening Balance Equity', 'acct_type' => 'CAPITAL',
            'gl_cat_no' => '3000000', 'gl_sub_cat_no' => '3100000', 'gl_type_no' => '3109000',
            'param_cd' => 'OBE', 'param_name' => 'Opening Balance Equity'];

        if (!DB::table('gl_acct_bks')->where('gl_no', $a['gl_no'])->exists()) {
            DB::table('gl_acct_bks')->insert([
                'gl_no' => $a['gl_no'], 'description' => $a['description'], 'acct_type' => $a['acct_type'],
                'gl_cat_no' => $a['gl_cat_no'], 'gl_sub_cat_no' => $a['gl_sub_cat_no'], 'gl_type_no' => $a['gl_type_no'],
                'status' => 'Active', 'created_on' => $now, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $branches = DB::table('gl_accounts')->select('institution_id', 'branch_id', 'branch_cd')->distinct()->get();
        foreach ($branches as $b) {
            $acctNo = $b->institution_id . '-' . $b->branch_cd . '-000-000-' . $a['gl_no'];
            if (!DB::table('gl_accounts')->where('acct_no', $acctNo)->exists()) {
                DB::table('gl_accounts')->insert([
                    'acct_no' => $acctNo, 'gl_no' => $a['gl_no'], 'description' => $a['description'],
                    'gl_cat_no' => $a['gl_cat_no'], 'gl_sub_cat_no' => $a['gl_sub_cat_no'], 'gl_type_no' => $a['gl_type_no'],
                    'acct_type' => $a['acct_type'], 'branch_cd' => $b->branch_cd, 'status' => 'Active',
                    'institution_id' => $b->institution_id, 'branch_id' => $b->branch_id,
                    'created_on' => $now, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
            if (!DB::table('gl_balances')->where('acct_no', $acctNo)->exists()) {
                DB::table('gl_balances')->insert([
                    'acct_no' => $acctNo, 'acct_type' => $a['acct_type'], 'balance' => 0,
                    'branch_cd' => $b->branch_cd, 'status' => 'Active',
                    'institution_id' => $b->institution_id, 'branch_id' => $b->branch_id,
                    'created_on' => $now, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        $instIds = DB::table('gl_accounts')->distinct()->pluck('institution_id');
        foreach ($instIds as $instId) {
            $exists = DB::table('cntrl_parameters')
                ->where('institution_id', $instId)->where('param_cd', $a['param_cd'])->exists();
            if (!$exists) {
                DB::table('cntrl_parameters')->insert([
                    'param_name' => $a['param_name'], 'param_cd' => $a['param_cd'],
                    'param_value' => $instId . '-***-000-000-' . $a['gl_no'],
                    'status' => 'Active', 'institution_id' => $instId,
                    'created_on' => $now, 'created_at' => $now, 'updated_at' => $now,
                ]);
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
        $acctNos = DB::table('gl_accounts')->where('gl_no', '3109001')->pluck('acct_no');
        DB::table('gl_balances')->whereIn('acct_no', $acctNos)->delete();
        DB::table('gl_accounts')->where('gl_no', '3109001')->delete();
        DB::table('gl_acct_bks')->where('gl_no', '3109001')->delete();
        DB::table('cntrl_parameters')->where('param_cd', 'OBE')->delete();
    }
};
