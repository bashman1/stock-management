<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Institution;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Admin "Accounting Health": scan one business's books branch by branch for the
 * known posting defects and apply the selected repairs. The ledger rows
 * (gl_histories) are the source of truth; cached balances and the stock
 * sub-ledger are brought back in line with them. Every repair runs inside one
 * database transaction and is logged to accounting_fix_logs with the affected
 * row ids and old values.
 */
class AccountingHealthController extends Controller
{
    // control parameters every branch needs, with their chart-of-accounts gl_no
    private const REQUIRED_PARAMS = [
        'CL' => '1301001', 'CGL' => '1302001', 'AR' => '1303001', 'STI' => '1304001', 'GFS' => '1307001',
        'AP' => '2202001', 'TAX' => '2203001', 'OBE' => '3109001', 'SL' => '4040001', 'SVR' => '4240001',
        'DGS' => '4250001', 'PPV' => '5270001', 'SAD' => '5280001',
    ];

    // repairs run in this order; BALANCE_DRIFT always runs last
    private const FIX_ORDER = [
        'MISSING_ACCOUNTS', 'PHANTOM_MEMO', 'DELETED_SALE_ROWS', 'DELETED_SALE_RECEIVABLES', 'OPENING_STOCK_IN_CASH',
        'TIMEZONE_DATES', 'SALE_ROW_LABELS', 'STOCK_LAYER_MISMATCH', 'INVENTORY_GL_GAP', 'BALANCE_DRIFT',
    ];

    private const LIVE = "COALESCE(H.reversal_flag, 'N') = 'N'";
    private const SIGNED = "CASE WHEN (A.acct_type IN ('ASSET', 'EXPENSE')) = (H.dr_cr_ind = 'Dr') THEN H.tran_amount ELSE -H.tran_amount END";

    private function forbidden($request)
    {
        if (Auth::user()->institution_id !== null) {
            return $this->genericResponse(false, 'Only system administrators can use Accounting Health', 403, null, 'accountingHealth', $request);
        }
        return null;
    }

    public function getBusinesses(Request $request)
    {
        if ($denied = $this->forbidden($request)) {
            return $denied;
        }
        $rows = DB::select("SELECT I.id, I.name, I.status,
                (SELECT COUNT(*) FROM branches B WHERE B.institution_id = I.id) AS branches,
                (SELECT COUNT(*) FROM gl_histories H WHERE H.institution_id = I.id) AS ledger_rows,
                (SELECT MAX(L.created_on) FROM accounting_fix_logs L WHERE L.institution_id = I.id) AS last_fixed_on
            FROM institutions I ORDER BY I.name");
        return $this->genericResponse(true, 'Businesses', 200, $rows, 'getBusinesses', $request);
    }

    public function scanBusiness(Request $request)
    {
        if ($denied = $this->forbidden($request)) {
            return $denied;
        }
        $institution = Institution::find($request->institution_id);
        if (!isset($institution)) {
            return $this->genericResponse(false, 'Business not found', 404, null, 'scanBusiness', $request);
        }
        $branches = Branch::where('institution_id', $institution->id)->orderBy('id')->get();
        $result = [];
        foreach ($branches as $branch) {
            $result[] = $this->scanBranch($institution->id, $branch);
        }
        return $this->genericResponse(true, 'Scan complete', 200, [
            'institution' => ['id' => $institution->id, 'name' => $institution->name],
            'branches' => $result,
            'scanned_on' => now()->toDateTimeString(),
        ], 'scanBusiness', $request);
    }

    public function applyFixes(Request $request)
    {
        if ($denied = $this->forbidden($request)) {
            return $denied;
        }
        $branch = Branch::where(['id' => $request->branch_id, 'institution_id' => $request->institution_id])->first();
        if (!isset($branch)) {
            return $this->genericResponse(false, 'Branch not found', 404, null, 'applyFixes', $request);
        }
        $instId = (int) $branch->institution_id;
        $codes = array_values(array_intersect(self::FIX_ORDER, (array) $request->codes));
        if (count($codes) === 0) {
            return $this->genericResponse(false, 'Select at least one fix', 400, null, 'applyFixes', $request);
        }

        $logs = [];
        DB::beginTransaction();
        try {
            foreach ($codes as $code) {
                if ($code === 'BALANCE_DRIFT') {
                    continue;
                }
                $method = 'fix' . str_replace(' ', '', ucwords(strtolower(str_replace('_', ' ', $code))));
                $log = $this->$method($instId, $branch);
                if ($log) {
                    $logs[] = $log;
                }
            }
            // every repair changes ledger rows, so cached balances are always rebuilt
            $log = $this->fixBalanceDrift($instId, $branch);
            if ($log) {
                $logs[] = $log;
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, 'No changes were made: ' . $th->getMessage(), 500, null, 'applyFixes', $request);
        }

        return $this->genericResponse(true, count($logs) . ' repair(s) applied', 200, [
            'logs' => $logs,
            'branch' => $this->scanBranch($instId, $branch),
        ], 'applyFixes', $request);
    }

    public function getFixLogs(Request $request)
    {
        if ($denied = $this->forbidden($request)) {
            return $denied;
        }
        $rows = DB::select("SELECT L.id, L.branch_id, B.name AS branch, L.issue_code, L.summary, L.rows_affected, L.amount,
                L.tran_id, L.created_on, CONCAT(U.first_name, ' ', U.last_name) AS created_by
            FROM accounting_fix_logs L LEFT JOIN branches B ON B.id = L.branch_id LEFT JOIN users U ON U.id = L.created_by
            WHERE L.institution_id = ? ORDER BY L.id DESC LIMIT 200", [(int) $request->institution_id]);
        return $this->genericResponse(true, 'Fix logs', 200, $rows, 'getFixLogs', $request);
    }

    // ---------------------------------------------------------------- scanning

    /** Account numbers this branch posts to, resolved from its control parameters. */
    private function accounts($instId, $branch): array
    {
        $params = DB::table('cntrl_parameters')->where('institution_id', $instId)->pluck('param_value', 'param_cd');
        $acct = fn ($cd, $glNo) => isset($params[$cd])
            ? str_replace('***', $branch->code, $params[$cd])
            : "$instId-$branch->code-000-000-$glNo";
        return [
            'cash' => $acct('CL', '1301001'), 'inv' => $acct('STI', '1304001'), 'cogs' => $acct('GFS', '1307001'),
            'pia' => $acct('PIA', '1305001'), 'pil' => $acct('PIL', '2205001'), 'pp' => $acct('PP', '5020001'),
            'obe' => $acct('OBE', '3109001'), 'sad' => $acct('SAD', '5280001'),
        ];
    }

    /** Ledger vs cached balance for every account of the branch. */
    private function accountBalances($instId, $branchId)
    {
        return collect(DB::select("SELECT A.acct_no, A.description, A.acct_type, A.branch_cd, A.status,
                B.id AS balance_id, B.acct_type AS balance_type, COALESCE(B.balance, 0) AS stored,
                COALESCE(L.amount, 0) AS ledger
            FROM gl_accounts A
            LEFT JOIN gl_balances B ON B.acct_no = A.acct_no
            LEFT JOIN (SELECT H.acct_no, ROUND(CAST(SUM(" . self::SIGNED . ") AS NUMERIC), 2) AS amount
                FROM gl_histories H INNER JOIN gl_accounts A ON A.acct_no = H.acct_no
                WHERE A.institution_id = ? AND A.branch_id = ? AND " . self::LIVE . " GROUP BY H.acct_no) L ON L.acct_no = A.acct_no
            WHERE A.institution_id = ? AND A.branch_id = ?
            ORDER BY A.acct_no", [$instId, $branchId, $instId, $branchId]));
    }

    private function scanBranch($instId, $branch): array
    {
        $acct = $this->accounts($instId, $branch);
        $balances = $this->accountBalances($instId, $branch->id);
        $byType = fn ($col, $type) => round((float) $balances->where('acct_type', $type)->sum($col), 2);
        $check = fn ($col) => round($byType($col, 'ASSET') - $byType($col, 'LIABILITY') - $byType($col, 'CAPITAL') - $byType($col, 'EQUITY')
            - ($byType($col, 'INCOME') - $byType($col, 'EXPENSE')), 2);
        $ledgerOf = fn ($acctNo) => (float) ($balances->firstWhere('acct_no', $acctNo)->ledger ?? 0);

        $layers = DB::selectOne("SELECT COALESCE(SUM(remaining_quantity * unit_cost), 0) AS value FROM inventory_cost_layers
            WHERE branch_id = ? AND status = 'Active' AND remaining_quantity > 0", [$branch->id]);
        $stockValue = DB::selectOne("SELECT COALESCE(SUM(GREATEST(S.quantity, 0) * S.purchase_price), 0) AS value
            FROM stocks S INNER JOIN products P ON P.id = S.product_id WHERE S.branch_id = ? AND P.nature = 'STOCK'", [$branch->id]);

        $summary = [
            'total_assets' => $byType('ledger', 'ASSET'),
            'total_liabilities' => $byType('ledger', 'LIABILITY'),
            'total_equity' => $byType('ledger', 'CAPITAL') + $byType('ledger', 'EQUITY'),
            'total_income' => $byType('ledger', 'INCOME'),
            'total_expense' => $byType('ledger', 'EXPENSE'),
            'balance_check_ledger' => $check('ledger'),
            'balance_check_stored' => $check('stored'),
            'cash_in_hand' => $ledgerOf($acct['cash']),
            'inventory_gl' => $ledgerOf($acct['inv']),
            'inventory_layers' => round((float) $layers->value, 2),
            'stock_on_hand_value' => round((float) $stockValue->value, 2),
            'cost_of_goods_sold' => $ledgerOf($acct['cogs']),
        ];

        $issues = array_values(array_filter([
            $this->checkMissingAccounts($instId, $branch),
            $this->checkUnbalanced($instId, $branch),
            $this->checkPhantomMemo($instId, $branch, $acct),
            $this->checkDeletedSaleRows($branch),
            $this->checkDeletedSaleReceivables($branch),
            $this->checkOpeningStockInCash($branch, $acct, $summary['cash_in_hand']),
            $this->checkNegativeCash($summary['cash_in_hand']),
            $this->checkTimezoneDates($instId, $branch),
            $this->checkSaleRowLabels($instId, $branch),
            $this->checkStockLayerMismatch($branch),
            $this->checkNegativeStock($branch),
            $this->checkSuspiciousUnitCosts($branch),
            $this->checkInventoryGap($summary),
            $this->checkOrphanSales($branch),
            $this->checkBalanceDrift($balances),
        ]));

        return [
            'branch_id' => $branch->id,
            'branch' => $branch->name,
            'code' => $branch->code,
            'summary' => $summary,
            'issues' => $issues,
        ];
    }

    private function issue($code, $severity, $title, $description, $count, $amount, $fixLabel = null, $optional = false, $samples = []): array
    {
        return [
            'code' => $code, 'severity' => $severity, 'title' => $title, 'description' => $description,
            'count' => (int) $count, 'amount' => round((float) $amount, 2),
            'fixable' => $fixLabel !== null, 'fix_label' => $fixLabel, 'optional' => $optional,
            'samples' => array_slice(array_values((array) $samples), 0, 15),
        ];
    }

    private function checkMissingAccounts($instId, $branch)
    {
        $params = DB::table('cntrl_parameters')->where('institution_id', $instId)->pluck('param_value', 'param_cd');
        $missing = [];
        foreach (self::REQUIRED_PARAMS as $cd => $glNo) {
            $acctNo = isset($params[$cd]) ? str_replace('***', $branch->code, $params[$cd]) : "$instId-$branch->code-000-000-$glNo";
            $hasAcct = DB::table('gl_accounts')->where('acct_no', $acctNo)->exists();
            $hasBal = DB::table('gl_balances')->where('acct_no', $acctNo)->exists();
            if (!isset($params[$cd]) || !$hasAcct || !$hasBal) {
                $missing[] = ['param' => $cd, 'acct_no' => $acctNo,
                    'problem' => !isset($params[$cd]) ? 'control parameter missing' : (!$hasAcct ? 'GL account missing' : 'balance row missing')];
            }
        }
        if (!$missing) {
            return null;
        }
        if (!DB::table('gl_accounts')->where('branch_id', $branch->id)->exists()) {
            return $this->issue('MISSING_ACCOUNTS', 'error', 'Branch has no chart of accounts',
                'This branch was created without GL accounts, so any sale or stock entry made in it fails or posts nowhere.',
                count($missing), 0, 'Create the chart of accounts', false, $missing);
        }
        return $this->issue('MISSING_ACCOUNTS', 'error', 'Missing control accounts',
            'Sales, stock receipts or adjustments fail (or post nowhere) when these control parameters or accounts are missing.',
            count($missing), 0, 'Create from the chart template', false, $missing);
    }

    private function checkUnbalanced($instId, $branch)
    {
        $rows = DB::select("SELECT H.tran_id, MIN(H.tran_type) AS tran_type, MIN(H.transaction_date) AS date,
                ROUND(CAST(SUM(CASE WHEN H.dr_cr_ind = 'Dr' THEN H.tran_amount ELSE -H.tran_amount END) AS NUMERIC), 2) AS difference
            FROM gl_histories H INNER JOIN gl_accounts A ON A.acct_no = H.acct_no
            WHERE A.institution_id = ? AND A.branch_id = ? AND " . self::LIVE . "
            GROUP BY H.tran_id HAVING ABS(SUM(CASE WHEN H.dr_cr_ind = 'Dr' THEN H.tran_amount ELSE -H.tran_amount END)) > 0.01",
            [$instId, $branch->id]);
        if (!$rows) {
            return null;
        }
        return $this->issue('UNBALANCED_TRANSACTIONS', 'error', 'Transactions whose debits and credits differ',
            'Each of these transactions leaves this branch out of balance, usually a journal that posted one side to another branch\'s account. '
            . 'Correct them by hand with a journal entry in each branch.',
            count($rows), array_sum(array_map(fn ($r) => abs((float) $r->difference), $rows)), null, false, $rows);
    }

    private function checkPhantomMemo($instId, $branch, $acct)
    {
        $rows = DB::select("SELECT H.acct_no, A.description, H.dr_cr_ind, COUNT(*) AS rows, ROUND(CAST(SUM(H.tran_amount) AS NUMERIC), 2) AS amount
            FROM gl_histories H INNER JOIN gl_accounts A ON A.acct_no = H.acct_no
            WHERE H.acct_no IN (?, ?, ?) AND H.tran_cd = 'STI' AND " . self::LIVE . "
            GROUP BY H.acct_no, A.description, H.dr_cr_ind", [$acct['pia'], $acct['pil'], $acct['pp']]);
        if (!$rows) {
            return null;
        }
        $liability = array_sum(array_map(fn ($r) => $r->acct_no === $acct['pil'] ? (float) $r->amount : 0, $rows));
        return $this->issue('PHANTOM_MEMO', 'error', 'Duplicate purchase memo entries',
            'Every stock receipt also posted Prepaid Expenses (asset) against Payables to party (liability), and a "passage" expense against the same payable. '
            . 'Inventory was counted twice on the balance sheet against a liability nobody owes, and passage amounts inflated cost of sales.',
            array_sum(array_map(fn ($r) => (int) $r->rows, $rows)), $liability, 'Void the memo entries', false, $rows);
    }

    private function deletedSaleRowsQuery(): string
    {
        return "FROM gl_histories H INNER JOIN transactions T ON T.tran_id = H.tran_id
            INNER JOIN orders O ON O.ref_no = T.ext_ref AND O.institution_id = T.institution_id
            WHERE O.status = 'Deleted' AND O.branch_id = ? AND " . self::LIVE;
    }

    private function checkDeletedSaleRows($branch)
    {
        $rows = DB::select("SELECT O.receipt_no, O.total, COUNT(H.id) AS rows " . $this->deletedSaleRowsQuery() . " GROUP BY O.receipt_no, O.total", [$branch->id]);
        if (!$rows) {
            return null;
        }
        return $this->issue('DELETED_SALE_ROWS', 'error', 'Deleted sales still in the ledger',
            'These sales were deleted but some of their postings are still live, so their revenue, cash and cost of sales still count.',
            count($rows), array_sum(array_map(fn ($r) => (float) $r->total, $rows)), 'Reverse the leftover postings', false, $rows);
    }

    private function checkDeletedSaleReceivables($branch)
    {
        $rows = DB::select("SELECT R.id, O.receipt_no, R.amount, R.amount_paid FROM customer_receivables R
            INNER JOIN orders O ON O.tran_id = R.tran_id AND O.institution_id = R.institution_id
            WHERE O.status = 'Deleted' AND O.branch_id = ? AND R.status <> 'Deleted'", [$branch->id]);
        if (!$rows) {
            return null;
        }
        return $this->issue('DELETED_SALE_RECEIVABLES', 'warning', 'Receivables for deleted credit sales',
            'Customers still show as owing for credit sales that were deleted.',
            count($rows), array_sum(array_map(fn ($r) => (float) $r->amount - (float) $r->amount_paid, $rows)), 'Close the receivables', false, $rows);
    }

    private function openingStockCashQuery(): string
    {
        return "FROM gl_histories H INNER JOIN transactions T ON T.tran_id = H.tran_id
            WHERE T.tran = 'Create Product' AND T.tran_cd = 'STI' AND H.acct_no = ? AND H.dr_cr_ind = 'Cr' AND " . self::LIVE;
    }

    private function checkOpeningStockInCash($branch, $acct, $cash)
    {
        $row = DB::selectOne("SELECT COUNT(*) AS rows, COALESCE(SUM(H.tran_amount), 0) AS amount " . $this->openingStockCashQuery(), [$acct['cash']]);
        if ((int) $row->rows === 0) {
            return null;
        }
        return $this->issue('OPENING_STOCK_IN_CASH', $cash < 0 ? 'error' : 'warning', 'Opening stock paid from cash in hand',
            'Stock entered when products were created (and bulk uploads) was credited to Cash in hand. If that stock was already owned, '
            . 'the credit belongs to Opening Balance Equity. Cash in hand would move from ' . number_format($cash, 2)
            . ' to ' . number_format($cash + (float) $row->amount, 2) . '. Only apply this if the business did not actually pay cash for that stock.',
            $row->rows, $row->amount, 'Move to Opening Balance Equity', true);
    }

    private function checkNegativeCash($cash)
    {
        if ($cash >= 0) {
            return null;
        }
        return $this->issue('NEGATIVE_CASH', 'warning', 'Cash in hand is negative',
            'A till cannot hold less than nothing. Usually opening stock or purchases were credited to cash; see "Opening stock paid from cash in hand".',
            1, $cash);
    }

    /**
     * Rows saved before the date fix was deployed hold browser UTC times. The
     * deploy time is when this business got its OBE parameter (added by the same
     * release); null once the branch has been repaired.
     */
    private function timezoneCutoff($instId, $branchId)
    {
        $repaired = DB::table('accounting_fix_logs')
            ->where(['institution_id' => $instId, 'branch_id' => $branchId, 'issue_code' => 'TIMEZONE_DATES'])->exists();
        if ($repaired) {
            return null;
        }
        return DB::table('cntrl_parameters')->where(['institution_id' => $instId, 'param_cd' => 'OBE'])->value('created_on')
            ?? now()->toDateTimeString();
    }

    private function timezoneCandidates($instId, $branch)
    {
        $cutoff = $this->timezoneCutoff($instId, $branch->id);
        if (!$cutoff) {
            return [];
        }
        // Entered in the browser and stored as UTC: anything not stamped at (or near)
        // the moment it was saved and not a plain midnight date. Server-stamped rows
        // are within seconds of created_on.
        return DB::select("SELECT T.id, T.tran_id, T.ext_ref, T.tran_date FROM transactions T
            WHERE T.institution_id = ? AND T.branch_id = ? AND T.tran_cd IN ('STI', 'STO')
            AND T.tran_date IS NOT NULL AND T.created_on IS NOT NULL AND T.created_on < ?
            AND ABS(EXTRACT(EPOCH FROM (T.created_on - T.tran_date))) > 600
            AND T.tran_date::time <> '00:00:00'", [$instId, $branch->id, $cutoff]);
    }

    private function checkTimezoneDates($instId, $branch)
    {
        $rows = $this->timezoneCandidates($instId, $branch);
        if (!$rows) {
            return null;
        }
        $hours = now()->utcOffset() / 60;
        $nextDay = count(array_filter($rows, fn ($r) => substr($r->tran_date, 11, 8) >= sprintf('%02d:00:00', 24 - $hours)));
        return $this->issue('TIMEZONE_DATES', 'warning', 'Dates stored in UTC instead of local time',
            "Dates picked in the browser were saved in UTC, $hours hours behind local time. $nextDay of these land on the previous day, "
            . 'so they fall into the wrong day (or month) in reports. The fix moves these sales and stock receipts forward by the timezone offset. Run it once per branch.',
            count($rows), 0, "Shift forward $hours hours", true,
            array_map(fn ($r) => ['ref' => $r->ext_ref, 'stored' => $r->tran_date], array_slice($rows, 0, 15)));
    }

    private function checkSaleRowLabels($instId, $branch)
    {
        $row = DB::selectOne("SELECT COUNT(*) AS rows FROM gl_histories H INNER JOIN gl_accounts A ON A.acct_no = H.acct_no
            WHERE A.institution_id = ? AND A.branch_id = ? AND H.tran_cd = 'STO' AND H.tran_type = 'STOCK IN'", [$instId, $branch->id]);
        if ((int) $row->rows === 0) {
            return null;
        }
        return $this->issue('SALE_ROW_LABELS', 'info', 'Sale postings labelled "STOCK IN"',
            'Revenue and cost-of-sales rows of sales were labelled as stock receipts. Totals are unaffected, but ledger filters by type mislead.',
            $row->rows, 0, 'Relabel as STOCK OUT');
    }

    private function layerMismatchRows($branch)
    {
        return DB::select("SELECT S.product_id, P.name, S.quantity, S.purchase_price,
                COALESCE(L.qty, 0) AS layer_qty, ROUND(CAST(COALESCE(L.val, 0) AS NUMERIC), 2) AS layer_value
            FROM stocks S INNER JOIN products P ON P.id = S.product_id
            LEFT JOIN (SELECT product_id, SUM(remaining_quantity) AS qty, SUM(remaining_quantity * unit_cost) AS val
                FROM inventory_cost_layers WHERE branch_id = ? AND status = 'Active' AND remaining_quantity > 0
                GROUP BY product_id) L ON L.product_id = S.product_id
            WHERE S.branch_id = ? AND P.nature = 'STOCK'
            AND ABS(GREATEST(S.quantity, 0) - COALESCE(L.qty, 0)) > 0.0001", [$branch->id, $branch->id]);
    }

    private function checkStockLayerMismatch($branch)
    {
        $rows = $this->layerMismatchRows($branch);
        if (!$rows) {
            return null;
        }
        return $this->issue('STOCK_LAYER_MISMATCH', 'warning', 'Cost layers out of step with stock on hand',
            'The costing ledger holds a different quantity than the product stock. Sales of these products are costed from the wrong quantities.',
            count($rows), 0, 'Rebuild layers from stock on hand', false, $rows);
    }

    private function checkNegativeStock($branch)
    {
        $rows = DB::select("SELECT S.product_id, P.name, S.quantity FROM stocks S INNER JOIN products P ON P.id = S.product_id
            WHERE S.branch_id = ? AND P.nature = 'STOCK' AND S.quantity < 0", [$branch->id]);
        if (!$rows) {
            return null;
        }
        return $this->issue('NEGATIVE_STOCK', 'warning', 'Products with negative stock',
            'More was sold than was received. Correct these with a stock count (edit the product quantity) after checking the shelf.',
            count($rows), 0, null, false, $rows);
    }

    private function checkSuspiciousUnitCosts($branch)
    {
        // a unit cost several times the selling price is almost always the total
        // cost of the delivery typed into the per-unit field
        $rows = DB::select("SELECT P.name, SH.source_type, SH.quantity, SH.purchase_price, SH.selling_price,
                ROUND(CAST(SH.quantity * SH.purchase_price AS NUMERIC), 2) AS posted, SH.created_on
            FROM stock_histories SH INNER JOIN products P ON P.id = SH.product_id
            WHERE SH.branch_id = ? AND SH.source_type IN ('OPENING', 'RESTOCK') AND SH.quantity > 0
            AND SH.selling_price > 0 AND SH.purchase_price > 3 * SH.selling_price
            ORDER BY SH.quantity * SH.purchase_price DESC", [$branch->id]);
        if (!$rows) {
            return null;
        }
        return $this->issue('SUSPICIOUS_UNIT_COSTS', 'warning', 'Stock received at a unit cost far above the selling price',
            'These receipts cost more than 3x the selling price per unit, which usually means the delivery total was typed as the unit price. '
            . 'Each posted quantity x price to inventory and cash, inflating both. Confirm with the business, then correct the product cost '
            . '(editing the product revalues the stock) and the payment with a journal entry.',
            count($rows), array_sum(array_map(fn ($r) => (float) $r->posted, $rows)), null, false, $rows);
    }

    private function checkInventoryGap(array $summary)
    {
        $gap = round($summary['inventory_layers'] - $summary['inventory_gl'], 2);
        if (abs($gap) < 1) {
            return null;
        }
        return $this->issue('INVENTORY_GL_GAP', 'error', 'Inventory account differs from stock on hand',
            'The Inventories account holds ' . number_format($summary['inventory_gl'], 2) . ' but the stock on hand is valued at '
            . number_format($summary['inventory_layers'], 2) . '. Caused by edits that rewrote old postings and sales costed at old prices. '
            . 'The fix posts the difference as a prior-period correction against Opening Balance Equity (not as a current expense). '
            . 'It runs after the other fixes and the amount is recalculated when it runs.',
            1, $gap, 'Post a stock adjustment');
    }

    private function checkOrphanSales($branch)
    {
        $rows = DB::select("SELECT O.id, O.receipt_no, O.total, O.tran_date FROM orders O
            WHERE O.branch_id = ? AND O.status = 'Active'
            AND NOT EXISTS (SELECT 1 FROM transactions T INNER JOIN gl_histories H ON H.tran_id = T.tran_id AND " . self::LIVE . "
                WHERE T.ext_ref = O.ref_no AND T.institution_id = O.institution_id)", [$branch->id]);
        if (!$rows) {
            return null;
        }
        return $this->issue('SALES_WITHOUT_POSTINGS', 'warning', 'Sales with no ledger postings',
            'These sales exist but never reached the ledger (for example a failed save), so they are missing from revenue and cash.',
            count($rows), array_sum(array_map(fn ($r) => (float) $r->total, $rows)), null, false, $rows);
    }

    private function checkBalanceDrift($balances)
    {
        $drift = $balances->filter(fn ($b) => $b->balance_id === null || $b->balance_type !== $b->acct_type
            || abs((float) $b->stored - (float) $b->ledger) > 0.005)->values();
        if ($drift->isEmpty()) {
            return null;
        }
        return $this->issue('BALANCE_DRIFT', 'error', 'Cached balances differ from the ledger',
            'Account balances shown on reports and the dashboard were updated separately from the ledger and no longer match it '
            . '(older sale reversals and product edits). This is why the balance sheet did not balance.',
            $drift->count(), $drift->sum(fn ($b) => abs((float) $b->stored - (float) $b->ledger)), 'Recalculate from the ledger', false,
            $drift->map(fn ($b) => ['acct_no' => $b->acct_no, 'description' => $b->description, 'stored' => (float) $b->stored, 'ledger' => (float) $b->ledger])->all());
    }

    // ------------------------------------------------------------------ repairs

    private function logFix($instId, $branch, $code, $summary, $rows, $amount, $details = null, $tranId = null): array
    {
        $row = [
            'institution_id' => $instId, 'branch_id' => $branch->id, 'issue_code' => $code, 'summary' => $summary,
            'rows_affected' => (int) $rows, 'amount' => round((float) $amount, 2),
            'details' => $details === null ? null : json_encode($details), 'tran_id' => $tranId,
            'created_by' => Auth::id(), 'created_on' => now(), 'created_at' => now(), 'updated_at' => now(),
        ];
        $row['id'] = DB::table('accounting_fix_logs')->insertGetId($row);
        unset($row['details']);
        return $row;
    }

    /** Posting identity for repair journals: the admin, booked to the branch. */
    private function poster($instId, $branch)
    {
        return (object) ['id' => Auth::id(), 'institution_id' => $instId, 'branch_id' => $branch->id];
    }

    private function fixMissingAccounts($instId, $branch)
    {
        $created = [];
        if (!DB::table('gl_accounts')->where('branch_id', $branch->id)->exists()) {
            $this->createBranchGlAccounts($instId, $branch->code, $branch->id);
            $created[] = "chart of accounts for branch $branch->code";
        }
        foreach (self::REQUIRED_PARAMS as $cd => $glNo) {
            $param = DB::table('cntrl_parameters')->where(['institution_id' => $instId, 'param_cd' => $cd])->first();
            $template = DB::table('gl_acct_bks')->where('gl_no', $glNo)->first();
            if (!isset($param)) {
                if (!isset($template)) {
                    continue;
                }
                DB::table('cntrl_parameters')->insert([
                    'param_name' => $template->description, 'param_cd' => $cd, 'param_value' => "$instId-***-000-000-$glNo",
                    'status' => 'Active', 'institution_id' => $instId, 'created_on' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                $created[] = "parameter $cd";
                $acctNo = "$instId-$branch->code-000-000-$glNo";
            } else {
                $acctNo = str_replace('***', $branch->code, $param->param_value);
                // the param may point at another gl_no than the default
                $template = DB::table('gl_acct_bks')->where('gl_no', substr($acctNo, -7))->first() ?? $template;
            }
            $account = DB::table('gl_accounts')->where('acct_no', $acctNo)->first();
            if (!isset($account) && isset($template)) {
                DB::table('gl_accounts')->insert([
                    'acct_no' => $acctNo, 'gl_no' => $template->gl_no, 'description' => $template->description,
                    'gl_cat_no' => $template->gl_cat_no, 'gl_sub_cat_no' => $template->gl_sub_cat_no, 'gl_type_no' => $template->gl_type_no,
                    'acct_type' => $template->acct_type, 'branch_cd' => $branch->code, 'status' => 'Active',
                    'institution_id' => $instId, 'branch_id' => $branch->id, 'created_on' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                $account = DB::table('gl_accounts')->where('acct_no', $acctNo)->first();
                $created[] = "account $acctNo";
            }
            if (isset($account) && !DB::table('gl_balances')->where('acct_no', $acctNo)->exists()) {
                DB::table('gl_balances')->insert([
                    'acct_no' => $acctNo, 'acct_type' => $account->acct_type, 'balance' => 0, 'branch_cd' => $branch->code,
                    'status' => 'Active', 'institution_id' => $instId, 'branch_id' => $branch->id,
                    'created_on' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                $created[] = "balance row $acctNo";
            }
        }
        if (!$created) {
            return null;
        }
        return $this->logFix($instId, $branch, 'MISSING_ACCOUNTS', 'Created ' . implode(', ', $created), count($created), 0, $created);
    }

    private function fixPhantomMemo($instId, $branch)
    {
        $acct = $this->accounts($instId, $branch);
        $rows = DB::select("SELECT H.id, H.tran_id, H.acct_no, H.dr_cr_ind, H.tran_amount FROM gl_histories H
            WHERE H.acct_no IN (?, ?, ?) AND H.tran_cd = 'STI' AND " . self::LIVE, [$acct['pia'], $acct['pil'], $acct['pp']]);
        if (!$rows) {
            return null;
        }
        // the memo legs must cancel out, or voiding them would unbalance the books
        $net = round(array_sum(array_map(fn ($r) => $r->dr_cr_ind === 'Dr' ? (float) $r->tran_amount : -(float) $r->tran_amount, $rows)), 2);
        if (abs($net) > 0.01) {
            throw new \RuntimeException("Purchase memo entries do not net to zero (off by $net); review them by hand.");
        }
        $ids = array_map(fn ($r) => $r->id, $rows);
        foreach (array_chunk($ids, 1000) as $chunk) {
            DB::table('gl_histories')->whereIn('id', $chunk)->update([
                'reversal_flag' => 'Y', 'status' => 'Voided', 'updated_by' => Auth::id(), 'updated_on' => now(),
            ]);
        }
        $liability = array_sum(array_map(fn ($r) => $r->acct_no === $acct['pil'] ? (float) $r->tran_amount : 0, $rows));
        return $this->logFix($instId, $branch, 'PHANTOM_MEMO', 'Voided ' . count($ids) . ' purchase memo / passage postings',
            count($ids), $liability, ['gl_history_ids' => $ids]);
    }

    private function fixDeletedSaleRows($instId, $branch)
    {
        $ids = array_map(fn ($r) => $r->id, DB::select("SELECT H.id " . $this->deletedSaleRowsQuery(), [$branch->id]));
        if (!$ids) {
            return null;
        }
        $amount = DB::table('gl_histories')->whereIn('id', $ids)->where('dr_cr_ind', 'Dr')->sum('tran_amount');
        foreach (array_chunk($ids, 1000) as $chunk) {
            DB::table('gl_histories')->whereIn('id', $chunk)->update([
                'reversal_flag' => 'Y', 'status' => 'Deleted', 'updated_by' => Auth::id(), 'updated_on' => now(),
            ]);
        }
        return $this->logFix($instId, $branch, 'DELETED_SALE_ROWS', 'Reversed ' . count($ids) . ' leftover postings of deleted sales',
            count($ids), $amount, ['gl_history_ids' => $ids]);
    }

    private function fixDeletedSaleReceivables($instId, $branch)
    {
        $ids = array_map(fn ($r) => $r->id, DB::select("SELECT R.id FROM customer_receivables R
            INNER JOIN orders O ON O.tran_id = R.tran_id AND O.institution_id = R.institution_id
            WHERE O.status = 'Deleted' AND O.branch_id = ? AND R.status <> 'Deleted'", [$branch->id]));
        if (!$ids) {
            return null;
        }
        $amount = DB::table('customer_receivables')->whereIn('id', $ids)->sum(DB::raw('amount - amount_paid'));
        DB::table('customer_receivables')->whereIn('id', $ids)->update(['status' => 'Deleted', 'updated_by' => Auth::id(), 'updated_on' => now()]);
        return $this->logFix($instId, $branch, 'DELETED_SALE_RECEIVABLES', 'Closed ' . count($ids) . ' receivables of deleted sales',
            count($ids), $amount, ['customer_receivable_ids' => $ids]);
    }

    private function fixOpeningStockInCash($instId, $branch)
    {
        $acct = $this->accounts($instId, $branch);
        $obe = DB::table('gl_accounts')->where('acct_no', $acct['obe'])->first();
        if (!isset($obe)) {
            throw new \RuntimeException('Opening Balance Equity account is missing; run "Missing control accounts" first.');
        }
        $rows = DB::select("SELECT H.id, H.tran_id, H.tran_amount " . $this->openingStockCashQuery(), [$acct['cash']]);
        if (!$rows) {
            return null;
        }
        $ids = array_map(fn ($r) => $r->id, $rows);
        $tranIds = array_values(array_unique(array_map(fn ($r) => $r->tran_id, $rows)));
        foreach (array_chunk($ids, 1000) as $chunk) {
            DB::table('gl_histories')->whereIn('id', $chunk)->update([
                'acct_no' => $obe->acct_no, 'updated_by' => Auth::id(), 'updated_on' => now(),
            ]);
        }
        foreach (array_chunk($tranIds, 1000) as $chunk) {
            // the inventory legs and the transaction records name the old contra account
            DB::table('gl_histories')->whereIn('tran_id', $chunk)->where('contra_acct_no', $acct['cash'])
                ->update(['contra_acct_no' => $obe->acct_no, 'contra_acct_type' => $obe->acct_type]);
            DB::table('transactions')->whereIn('tran_id', $chunk)->where('contra_acct_no', $acct['cash'])
                ->update(['contra_acct_no' => $obe->acct_no, 'contra_acct_type' => $obe->acct_type]);
        }
        $amount = array_sum(array_map(fn ($r) => (float) $r->tran_amount, $rows));
        return $this->logFix($instId, $branch, 'OPENING_STOCK_IN_CASH',
            'Moved ' . count($ids) . " opening stock credits from Cash in hand ({$acct['cash']}) to Opening Balance Equity",
            count($ids), $amount, ['gl_history_ids' => $ids, 'from_acct' => $acct['cash'], 'to_acct' => $obe->acct_no]);
    }

    private function fixTimezoneDates($instId, $branch)
    {
        $rows = $this->timezoneCandidates($instId, $branch);
        if (!$rows) {
            return null;
        }
        $minutes = (int) now()->utcOffset();
        $ids = array_map(fn ($r) => $r->id, $rows);
        foreach (array_chunk($ids, 1000) as $chunk) {
            $list = implode(',', array_map('intval', $chunk));
            // ledger rows and orders still carry the old value: match on it so rows
            // posted later under the same tran_id (e.g. reversal adjustments) stay put
            DB::update("UPDATE gl_histories H SET transaction_date = H.transaction_date + INTERVAL '$minutes minutes'
                FROM transactions T WHERE T.id IN ($list) AND H.tran_id = T.tran_id AND H.transaction_date = T.tran_date");
            DB::update("UPDATE orders O SET tran_date = O.tran_date + INTERVAL '$minutes minutes'
                FROM transactions T WHERE T.id IN ($list) AND T.tran_cd = 'STO' AND O.ref_no = T.ext_ref
                AND O.institution_id = T.institution_id AND O.tran_date = T.tran_date");
            DB::update("UPDATE transactions SET tran_date = tran_date + INTERVAL '$minutes minutes' WHERE id IN ($list)");
        }
        return $this->logFix($instId, $branch, 'TIMEZONE_DATES', 'Moved ' . count($ids) . " sales and stock receipts forward $minutes minutes to local time",
            count($ids), 0, ['transaction_ids' => $ids, 'minutes' => $minutes]);
    }

    private function fixSaleRowLabels($instId, $branch)
    {
        $count = DB::update("UPDATE gl_histories H SET tran_type = 'STOCK OUT' FROM gl_accounts A
            WHERE A.acct_no = H.acct_no AND A.institution_id = ? AND A.branch_id = ? AND H.tran_cd = 'STO' AND H.tran_type = 'STOCK IN'",
            [$instId, $branch->id]);
        if (!$count) {
            return null;
        }
        return $this->logFix($instId, $branch, 'SALE_ROW_LABELS', "Relabelled $count sale postings as STOCK OUT", $count, 0);
    }

    private function fixStockLayerMismatch($instId, $branch)
    {
        $rows = $this->layerMismatchRows($branch);
        if (!$rows) {
            return null;
        }
        $details = [];
        foreach ($rows as $r) {
            $open = DB::table('inventory_cost_layers')->where(['product_id' => $r->product_id, 'branch_id' => $branch->id, 'status' => 'Active'])
                ->where('remaining_quantity', '>', 0);
            $closed = $open->pluck('id')->all();
            $open->update(['remaining_quantity' => 0, 'updated_on' => now()]);

            $qty = max((float) $r->quantity, 0);
            $unit = (float) $r->purchase_price > 0 ? (float) $r->purchase_price
                : ((float) $r->layer_qty > 0 ? (float) $r->layer_value / (float) $r->layer_qty : 0);
            if ($qty > 0) {
                DB::table('inventory_cost_layers')->insert([
                    'product_id' => $r->product_id, 'branch_id' => $branch->id, 'institution_id' => $instId,
                    'source_type' => 'REBUILD', 'source_ref' => 'accounting-health', 'receipt_date' => now(),
                    'quantity' => $qty, 'remaining_quantity' => $qty, 'unit_cost' => $unit, 'status' => 'Active',
                    'created_by' => Auth::id(), 'created_on' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $details[] = ['product_id' => $r->product_id, 'old_layer_qty' => (float) $r->layer_qty, 'new_qty' => $qty,
                'unit_cost' => $unit, 'closed_layer_ids' => $closed];
        }
        return $this->logFix($instId, $branch, 'STOCK_LAYER_MISMATCH', 'Rebuilt cost layers for ' . count($rows) . ' products from stock on hand',
            count($rows), 0, $details);
    }

    private function fixInventoryGlGap($instId, $branch)
    {
        $acct = $this->accounts($instId, $branch);
        $gl = (float) DB::selectOne("SELECT COALESCE(SUM(" . self::SIGNED . "), 0) AS v FROM gl_histories H
            INNER JOIN gl_accounts A ON A.acct_no = H.acct_no WHERE H.acct_no = ? AND " . self::LIVE, [$acct['inv']])->v;
        $layers = (float) DB::selectOne("SELECT COALESCE(SUM(remaining_quantity * unit_cost), 0) AS v FROM inventory_cost_layers
            WHERE branch_id = ? AND status = 'Active' AND remaining_quantity > 0", [$branch->id])->v;
        $gap = round($layers - $gl, 2);
        if (abs($gap) < 0.01) {
            return null;
        }
        // The gap comes from past postings being rewritten, so it is a prior-period
        // correction: it goes to equity, not to this period's expenses.
        $inv = DB::table('gl_accounts')->where('acct_no', $acct['inv'])->first();
        $obe = DB::table('gl_accounts')->where('acct_no', $acct['obe'])->first();
        if (!isset($inv) || !isset($obe)) {
            throw new \RuntimeException('Inventory or Opening Balance Equity account is missing; run "Missing control accounts" first.');
        }
        $tranId = $this->generateUuid();
        $desc = 'Accounting health: prior-period correction aligning Inventories with stock on hand';
        if ($gap > 0) {
            $this->postJournal($inv->acct_no, $inv->acct_type, $obe->acct_no, $obe->acct_type, $gap, $tranId, $desc, now(), $this->poster($instId, $branch), 'ADJ', 'Stock Adjustment');
        } else {
            $this->postJournal($obe->acct_no, $obe->acct_type, $inv->acct_no, $inv->acct_type, -$gap, $tranId, $desc, now(), $this->poster($instId, $branch), 'ADJ', 'Stock Adjustment');
        }
        return $this->logFix($instId, $branch, 'INVENTORY_GL_GAP',
            'Posted a prior-period correction of ' . number_format($gap, 2) . ' to Opening Balance Equity (Inventories '
            . number_format($gl, 2) . ' -> ' . number_format($layers, 2) . ')',
            2, $gap, ['inventory_before' => $gl, 'inventory_after' => $layers], $tranId);
    }

    private function fixBalanceDrift($instId, $branch)
    {
        $changed = [];
        foreach ($this->accountBalances($instId, $branch->id) as $b) {
            if ($b->balance_id === null) {
                DB::table('gl_balances')->insert([
                    'acct_no' => $b->acct_no, 'acct_type' => $b->acct_type, 'balance' => $b->ledger, 'branch_cd' => $b->branch_cd,
                    'status' => $b->status ?? 'Active', 'institution_id' => $instId, 'branch_id' => $branch->id,
                    'created_on' => now(), 'created_at' => now(), 'updated_at' => now(),
                ]);
                $changed[] = ['acct_no' => $b->acct_no, 'old' => null, 'new' => (float) $b->ledger];
            } elseif ($b->balance_type !== $b->acct_type || abs((float) $b->stored - (float) $b->ledger) > 0.005) {
                DB::table('gl_balances')->where('id', $b->balance_id)->update([
                    'balance' => $b->ledger, 'acct_type' => $b->acct_type, 'updated_on' => now(),
                ]);
                $changed[] = ['acct_no' => $b->acct_no, 'old' => (float) $b->stored, 'old_type' => $b->balance_type, 'new' => (float) $b->ledger];
            }
        }
        if (!$changed) {
            return null;
        }
        return $this->logFix($instId, $branch, 'BALANCE_DRIFT', 'Recalculated ' . count($changed) . ' account balances from the ledger',
            count($changed), array_sum(array_map(fn ($c) => abs((float) $c['new'] - (float) ($c['old'] ?? 0)), $changed)), $changed);
    }
}
