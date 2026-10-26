<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Reclassify GL account 1307001 (used as "Goods for sale"/GFS, the account COGS
     * is posted to on every sale) from ASSET to EXPENSE, renamed "Cost of Goods Sold"
     * and moved into the EXPENSES hierarchy. This makes cost of sales a real expense
     * — historically and going forward — so profit and retained earnings are correct
     * and the balance sheet balances. The acct_no/gl_no are kept stable so existing
     * control parameters and posted history continue to resolve.
     *
     * Applies to the chart template (gl_acct_bks) and every institution's live
     * gl_accounts and gl_balances rows.
     *
     * @return void
     */
    public function up()
    {
        $glNo = '1307001';
        $name = 'Cost of Goods Sold';

        // 1) chart template
        DB::table('gl_acct_bks')->where('gl_no', $glNo)->update([
            'description'   => $name,
            'acct_type'     => 'EXPENSE',
            'gl_cat_no'     => '5000000',
            'gl_sub_cat_no' => '5000000',
            'gl_type_no'    => '5230000',
        ]);

        // 2) live accounts (all institutions/branches)
        DB::table('gl_accounts')->where('gl_no', $glNo)->update([
            'description'   => $name,
            'acct_type'     => 'EXPENSE',
            'gl_cat_no'     => '5000000',
            'gl_sub_cat_no' => '5000000',
            'gl_type_no'    => '5230000',
        ]);

        // 3) balance snapshot rows for those accounts (acct_type must match)
        $acctNos = DB::table('gl_accounts')->where('gl_no', $glNo)->pluck('acct_no')->all();
        if (!empty($acctNos)) {
            DB::table('gl_balances')->whereIn('acct_no', $acctNos)->update(['acct_type' => 'EXPENSE']);
        }
    }

    /**
     * Reverse: restore the original ASSET classification.
     *
     * @return void
     */
    public function down()
    {
        $glNo = '1307001';
        $name = 'Advances to party';

        DB::table('gl_acct_bks')->where('gl_no', $glNo)->update([
            'description'   => $name,
            'acct_type'     => 'ASSET',
            'gl_cat_no'     => '1000000',
            'gl_sub_cat_no' => '1300000',
            'gl_type_no'    => '1307000',
        ]);

        DB::table('gl_accounts')->where('gl_no', $glNo)->update([
            'description'   => $name,
            'acct_type'     => 'ASSET',
            'gl_cat_no'     => '1000000',
            'gl_sub_cat_no' => '1300000',
            'gl_type_no'    => '1307000',
        ]);

        $acctNos = DB::table('gl_accounts')->where('gl_no', $glNo)->pluck('acct_no')->all();
        if (!empty($acctNos)) {
            DB::table('gl_balances')->whereIn('acct_no', $acctNos)->update(['acct_type' => 'ASSET']);
        }
    }
};
