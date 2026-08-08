<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * GL accounts the manufacturing module needs:
     *  - 1312001 Work-in-Progress (ASSET, param WIP) — holds material cost issued to
     *    production until the goods are finished.
     *  - 5260001 Production Scrap Loss (EXPENSE, param PSL) — value of WIP scrapped.
     * Finished goods and raw materials both keep using the existing Inventory
     * account (STI / 1304001). Adds them to the chart template, backfills
     * gl_accounts + gl_balances per institution/branch, and registers control
     * parameters (param_value uses *** for the branch code).
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        foreach (['gl_acct_bks', 'gl_accounts', 'gl_balances', 'cntrl_parameters'] as $t) {
            DB::statement("SELECT setval(pg_get_serial_sequence('$t', 'id'), COALESCE((SELECT MAX(id) FROM $t), 1))");
        }

        $accounts = [
            ['gl_no' => '1312001', 'description' => 'Work-in-Progress', 'acct_type' => 'ASSET',
                'gl_cat_no' => '1000000', 'gl_sub_cat_no' => '1300000', 'gl_type_no' => '1312000',
                'param_cd' => 'WIP', 'param_name' => 'Work in Progress'],
            ['gl_no' => '5260001', 'description' => 'Production Scrap Loss', 'acct_type' => 'EXPENSE',
                'gl_cat_no' => '5000000', 'gl_sub_cat_no' => '5000000', 'gl_type_no' => '5260000',
                'param_cd' => 'PSL', 'param_name' => 'Production Scrap Loss'],
        ];

        // 1) chart template
        foreach ($accounts as $a) {
            if (!DB::table('gl_acct_bks')->where('gl_no', $a['gl_no'])->exists()) {
                DB::table('gl_acct_bks')->insert([
                    'gl_no' => $a['gl_no'], 'description' => $a['description'], 'acct_type' => $a['acct_type'],
                    'gl_cat_no' => $a['gl_cat_no'], 'gl_sub_cat_no' => $a['gl_sub_cat_no'], 'gl_type_no' => $a['gl_type_no'],
                    'status' => 'Active', 'created_on' => $now, 'created_at' => $now, 'updated_at' => $now,
                ]);
            }
        }

        // 2) per institution/branch: gl_accounts + gl_balances
        $branches = DB::table('gl_accounts')
            ->select('institution_id', 'branch_id', 'branch_cd')
            ->distinct()->get();

        foreach ($branches as $b) {
            foreach ($accounts as $a) {
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
        }

        // 3) per institution: control parameters
        $instIds = DB::table('gl_accounts')->distinct()->pluck('institution_id');
        foreach ($instIds as $instId) {
            foreach ($accounts as $a) {
                $paramValue = $instId . '-***-000-000-' . $a['gl_no'];
                $exists = DB::table('cntrl_parameters')
                    ->where('institution_id', $instId)->where('param_cd', $a['param_cd'])->exists();
                if (!$exists) {
                    DB::table('cntrl_parameters')->insert([
                        'param_name' => $a['param_name'], 'param_cd' => $a['param_cd'], 'param_value' => $paramValue,
                        'status' => 'Active', 'institution_id' => $instId,
                        'created_on' => $now, 'created_at' => $now, 'updated_at' => $now,
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
        $glNos = ['1312001', '5260001'];
        $acctNos = DB::table('gl_accounts')->whereIn('gl_no', $glNos)->pluck('acct_no');
        DB::table('gl_balances')->whereIn('acct_no', $acctNos)->delete();
        DB::table('gl_accounts')->whereIn('gl_no', $glNos)->delete();
        DB::table('gl_acct_bks')->whereIn('gl_no', $glNos)->delete();
        DB::table('cntrl_parameters')->whereIn('param_cd', ['WIP', 'PSL'])->delete();
    }
};
