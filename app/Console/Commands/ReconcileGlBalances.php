<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReconcileGlBalances extends Command
{
    /**
     * @var string
     */
    protected $signature = 'gl:reconcile
        {--institution= : Limit to one institution id}
        {--branch= : Limit to one branch id}
        {--write : Persist the rebuilt balances (otherwise dry-run)}
        {--void-unbalanced : Mark partially-posted (unbalanced) live transactions as reversed before rebuilding (needs --write)}';

    /**
     * @var string
     */
    protected $description = 'Rebuild gl_balances from non-reversed gl_histories and report each branch\'s trial balance.';

    public function handle()
    {
        $instId = $this->option('institution');
        $branchId = $this->option('branch');
        $write = (bool) $this->option('write');

        $this->info($write ? 'Mode: WRITE (balances will be updated)' : 'Mode: DRY-RUN (no changes)');
        if ($write) {
            $this->warn('Make sure you have backed up gl_balances and gl_histories before writing.');
        }

        // 0) optionally void partially-posted (unbalanced) live transactions so the
        //    rebuild that follows excludes them and the books balance exactly.
        if ($this->option('void-unbalanced')) {
            $ids = collect($this->findUnbalanced($instId, $branchId))->pluck('tran_id')->all();
            if (empty($ids)) {
                $this->info('No unbalanced live transactions to void.');
            } elseif (!$write) {
                $this->warn('Would void ' . count($ids) . ' unbalanced transaction(s) (add --write to apply).');
            } else {
                $q = DB::table('gl_histories')->whereIn('tran_id', $ids)->whereRaw("COALESCE(reversal_flag,'N')='N'");
                if ($instId)  { $q->where('institution_id', $instId); }
                if ($branchId) { $q->where('branch_id', $branchId); }
                $n = $q->update(['reversal_flag' => 'Y', 'status' => 'Deleted', 'updated_on' => now()]);
                $this->info("Voided $n row(s) across " . count($ids) . " unbalanced transaction(s).");
            }
        }

        // 1) recompute each account's signed balance from live (reversal_flag='N') history
        $computeSql = "SELECT H.acct_no, B.acct_type,
            SUM(CASE
                WHEN B.acct_type IN ('ASSET','EXPENSE') AND H.dr_cr_ind = 'Dr' THEN H.tran_amount
                WHEN B.acct_type IN ('ASSET','EXPENSE') AND H.dr_cr_ind = 'Cr' THEN -H.tran_amount
                WHEN B.acct_type IN ('LIABILITY','INCOME','EQUITY','CAPITAL') AND H.dr_cr_ind = 'Cr' THEN H.tran_amount
                WHEN B.acct_type IN ('LIABILITY','INCOME','EQUITY','CAPITAL') AND H.dr_cr_ind = 'Dr' THEN -H.tran_amount
                ELSE 0 END) AS computed
            FROM gl_histories H
            INNER JOIN gl_balances B ON B.acct_no = H.acct_no
            WHERE COALESCE(H.reversal_flag, 'N') = 'N'";
        $bindings = [];
        if ($instId)  { $computeSql .= " AND H.institution_id = ?"; $bindings[] = $instId; }
        if ($branchId) { $computeSql .= " AND H.branch_id = ?"; $bindings[] = $branchId; }
        $computeSql .= " GROUP BY H.acct_no, B.acct_type";

        $computed = [];
        foreach (DB::select($computeSql, $bindings) as $row) {
            $computed[$row->acct_no] = (float) $row->computed;
        }

        // 2) walk every balance row in scope, compare and (optionally) update
        $balQuery = DB::table('gl_balances');
        if ($instId)  { $balQuery->where('institution_id', $instId); }
        if ($branchId) { $balQuery->where('branch_id', $branchId); }
        $balances = $balQuery->get();

        $changed = 0;
        $totals = []; // per "instId/branchId" => [type => sum]
        foreach ($balances as $bal) {
            $new = $computed[$bal->acct_no] ?? 0.0; // no live history => 0
            if (abs($new - (float) $bal->balance) > 0.01) {
                $changed++;
                if ($write) {
                    DB::table('gl_balances')->where('id', $bal->id)->update([
                        'balance' => $new,
                        'updated_on' => now(),
                    ]);
                }
            }
            $key = $bal->institution_id . '/' . $bal->branch_id;
            $totals[$key][$bal->acct_type] = ($totals[$key][$bal->acct_type] ?? 0) + $new;
        }

        $this->info("Accounts " . ($write ? 'updated' : 'that would change') . ": $changed");

        // 3) per-branch trial balance using the rebuilt figures
        $this->line('');
        $this->info('Trial balance per branch (rebuilt):');
        foreach ($totals as $key => $t) {
            $assets = $t['ASSET'] ?? 0;
            $liab = $t['LIABILITY'] ?? 0;
            $income = $t['INCOME'] ?? 0;
            $expense = $t['EXPENSE'] ?? 0;
            $equity = ($t['EQUITY'] ?? 0) + ($t['CAPITAL'] ?? 0);
            $lhs = $assets + $expense;
            $rhs = $liab + $income + $equity;
            $retained = $income - $expense;
            $bsDiff = $assets - ($liab + $equity + $retained);
            $this->line(sprintf(
                "  inst/branch %-8s Assets=%.2f Liab=%.2f Income=%.2f Expense=%.2f | A+E=%.2f L+I+Eq=%.2f diff=%.2f | BS(A - (L+Eq+RE))=%.2f",
                $key, $assets, $liab, $income, $expense, $lhs, $rhs, $lhs - $rhs, $bsDiff
            ));
        }

        // 4) list remaining unbalanced live transactions (genuine posting bugs to review)
        $unbalanced = $this->findUnbalanced($instId, $branchId);

        $this->line('');
        if (count($unbalanced) === 0) {
            $this->info('No unbalanced live transactions. Debits = credits.');
        } else {
            $this->warn('Unbalanced live (reversal_flag=N) transactions: ' . count($unbalanced) . ' (review these posting bugs)');
            $sum = 0;
            foreach (array_slice($unbalanced, 0, 15) as $u) {
                $sum += (float) $u->diff;
                $this->line(sprintf("  tran_id=%s diff=%.2f", $u->tran_id, $u->diff));
            }
            $this->line(sprintf("  net residual across all: %.2f", array_sum(array_map(fn($u) => (float) $u->diff, $unbalanced))));
        }

        return Command::SUCCESS;
    }

    /**
     * Live (reversal_flag='N') transactions whose debits != credits.
     */
    private function findUnbalanced($instId, $branchId)
    {
        $sql = "SELECT tran_id,
            SUM(CASE WHEN dr_cr_ind = 'Dr' THEN tran_amount ELSE -tran_amount END) AS diff
            FROM gl_histories WHERE COALESCE(reversal_flag, 'N') = 'N'";
        $b = [];
        if ($instId)  { $sql .= " AND institution_id = ?"; $b[] = $instId; }
        if ($branchId) { $sql .= " AND branch_id = ?"; $b[] = $branchId; }
        $sql .= " GROUP BY tran_id HAVING ABS(SUM(CASE WHEN dr_cr_ind = 'Dr' THEN tran_amount ELSE -tran_amount END)) > 0.01
            ORDER BY ABS(SUM(CASE WHEN dr_cr_ind = 'Dr' THEN tran_amount ELSE -tran_amount END)) DESC";
        return DB::select($sql, $b);
    }
}
