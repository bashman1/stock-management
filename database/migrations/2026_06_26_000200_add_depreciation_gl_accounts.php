<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

return new class extends Migration
{
    /**
     * Add the two GL accounts the asset module needs but the chart lacks:
     *  - 1113001 Accumulated Depreciation (ASSET, contra — credit balances net it
     *    against the fixed-asset accounts on the balance sheet)
     *  - 5250001 Depreciation Expense (EXPENSE)
     * Adds them to the chart template (gl_acct_bks), backfills gl_accounts +
     * gl_balances for every existing institution/branch, and registers the
     * AD / DEX control parameters per institution.
     *
     * @return void
     */
    public function up()
    {
        $now = Carbon::now();

        // Seed data was inserted with explicit ids, leaving the id sequences behind
        // max(id); realign them so our nextval-based inserts don't collide.
        foreach (['gl_acct_bks', 'gl_accounts', 'gl_balances', 'cntrl_parameters'] as $t) {
            DB::statement("SELECT setval(pg_get_serial_sequence('$t', 'id'), COALESCE((SELECT MAX(id) FROM $t), 1))");
        }

        $accounts = [
            ['gl_no' => '1113001', 'description' => 'Accumulated Depreciation', 'acct_type' => 'ASSET',
                'gl_cat_no' => '1000000', 'gl_sub_cat_no' => '1100000', 'gl_type_no' => '1103000',
                'param_cd' => 'AD', 'param_name' => 'Accumulated Depreciation'],
            ['gl_no' => '5250001', 'description' => 'Depreciation Expense', 'acct_type' => 'EXPENSE',
                'gl_cat_no' => '5000000', 'gl_sub_cat_no' => '5000000', 'gl_type_no' => '5230000',
                'param_cd' => 'DEX', 'param_name' => 'Depreciation Expense'],
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

        // 3) per institution: control parameters (param_value uses *** for the branch code)
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
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $glNos = ['1113001', '5250001'];
        $acctNos = DB::table('gl_accounts')->whereIn('gl_no', $glNos)->pluck('acct_no');
        DB::table('gl_balances')->whereIn('acct_no', $acctNos)->delete();
        DB::table('gl_accounts')->whereIn('gl_no', $glNos)->delete();
        DB::table('gl_acct_bks')->whereIn('gl_no', $glNos)->delete();
        DB::table('cntrl_parameters')->whereIn('param_cd', ['AD', 'DEX'])->delete();
    }
};
