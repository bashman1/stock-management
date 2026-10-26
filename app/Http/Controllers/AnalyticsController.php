<?php

namespace App\Http\Controllers;

use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    private function dateOr($value, $default)
    {
        if (empty($value)) {
            return $default;
        }
        $t = strtotime($value);
        return $t ? date('Y-m-d', $t) : $default;
    }

    /**
     * Comprehensive BI payload: KPI cards, a P&L time-series, top products,
     * sales by category, expense breakdown and activity volume — for a period,
     * scoped to the user's institution/branch.
     */
    public function getAnalytics(Request $request)
    {
        $userData = $this->getUserLogin();
        $inst = (int) $userData->institution_id;
        $branch = (int) ($request->branch_id ?: $userData->branch_id);

        $from = $this->dateOr($request->from, date('Y-m-d', strtotime('-29 days')));
        $to = $this->dateOr($request->to, date('Y-m-d'));

        // control accounts of the branch being viewed (not the user's own branch)
        $branchRow = \App\Models\Branch::where(['id' => $branch, 'institution_id' => $inst])->first();
        if (!isset($branchRow)) {
            return $this->genericResponse(false, 'Branch not found', 404, null, 'getAnalytics', $request);
        }
        $acct = fn ($cd) => $this->resolveControlAccount($cd, $inst, $branchRow->code)?->acct_no ?? '';
        $gfs = $acct('GFS'); // cost of goods sold
        $rin = $acct('RIN'); // returns inwards, a reduction of revenue

        // Natural-sign amounts: credits are income, debits are expense. Summing raw
        // tran_amount added both sides together.
        $signed = "CASE WHEN (A.acct_type IN ('ASSET', 'EXPENSE')) = (H.dr_cr_ind = 'Dr') THEN H.tran_amount ELSE -H.tran_amount END";
        $glWhere = "H.institution_id = $inst AND H.branch_id = $branch AND COALESCE(H.reversal_flag, 'N') = 'N'
            AND H.transaction_date::date BETWEEN '$from' AND '$to'";
        $glFrom = "FROM gl_histories H JOIN gl_accounts A ON A.acct_no = H.acct_no WHERE $glWhere";
        $kpi = DB::select("SELECT
            (SELECT COALESCE(SUM(CASE WHEN A.acct_type = 'INCOME' THEN $signed WHEN H.acct_no = '$rin' THEN -($signed) ELSE 0 END), 0) $glFrom) AS revenue,
            (SELECT COALESCE(SUM(CASE WHEN H.acct_no = '$gfs' THEN $signed ELSE 0 END), 0) $glFrom) AS cogs,
            (SELECT COALESCE(SUM(CASE WHEN A.acct_type = 'EXPENSE' AND H.acct_no NOT IN ('$gfs', '$rin') THEN $signed ELSE 0 END), 0) $glFrom) AS operating_expenses,
            (SELECT COUNT(id) FROM orders WHERE status='Active' AND institution_id=$inst AND branch_id=$branch AND tran_date::date BETWEEN '$from' AND '$to') AS orders_count,
            (SELECT COALESCE(SUM(remaining_quantity*unit_cost),0) FROM inventory_cost_layers WHERE branch_id=$branch AND status='Active' AND remaining_quantity>0) AS inventory_value,
            (SELECT COALESCE(SUM(amount-amount_paid),0) FROM customer_receivables WHERE status='Active' AND institution_id=$inst AND branch_id=$branch) AS receivables,
            (SELECT COALESCE(SUM(amount-amount_paid),0) FROM payables WHERE status='Active' AND institution_id=$inst AND branch_id=$branch) AS payables,
            (SELECT COUNT(S.id) FROM stocks S JOIN products P ON P.id = S.product_id WHERE S.branch_id=$branch AND S.institution_id=$inst
                AND P.status = 'Active' AND P.nature = 'STOCK' AND COALESCE(S.min_quantity,0)>0 AND S.quantity <= S.min_quantity) AS low_stock,
            (SELECT COUNT(id) FROM members WHERE status='Active' AND institution_id=$inst AND branch_id=$branch) AS customers
        ")[0];

        $revenue = (float) $kpi->revenue;
        $cogs = (float) $kpi->cogs;
        $operatingExpenses = (float) $kpi->operating_expenses;
        $kpis = [
            'revenue' => $revenue,
            'cogs' => $cogs,
            'gross_profit' => $revenue - $cogs,
            'operating_expenses' => $operatingExpenses,
            'net_profit' => $revenue - $cogs - $operatingExpenses,
            'inventory_value' => (float) $kpi->inventory_value,
            'receivables' => (float) $kpi->receivables,
            'payables' => (float) $kpi->payables,
            'low_stock' => (int) $kpi->low_stock,
            'orders_count' => (int) $kpi->orders_count,
            'customers' => (int) $kpi->customers,
        ];

        // ---- P&L time series (day grain for <=62 days, else month) ----
        $days = (strtotime($to) - strtotime($from)) / 86400;
        $grain = $days <= 62 ? 'day' : 'month';
        $series = DB::select("SELECT to_char(date_trunc('$grain', H.transaction_date), 'YYYY-MM-DD') AS bucket,
            COALESCE(SUM(CASE WHEN A.acct_type = 'INCOME' THEN $signed WHEN H.acct_no = '$rin' THEN -($signed) ELSE 0 END), 0) AS revenue,
            COALESCE(SUM(CASE WHEN H.acct_no = '$gfs' THEN $signed ELSE 0 END), 0) AS cogs,
            COALESCE(SUM(CASE WHEN A.acct_type = 'EXPENSE' AND H.acct_no NOT IN ('$gfs', '$rin') THEN $signed ELSE 0 END), 0) AS expenses
            $glFrom GROUP BY 1 ORDER BY 1");

        // ---- Top products / categories by revenue (sale lines in range) ----
        // older lines have no selling_price; price them like the dashboard does
        $price = $this->saleLinePriceSql();
        $linesFrom = "FROM order_items O JOIN orders Q ON Q.id = O.order_id " . $this->saleLineJoins() . "
            JOIN products P ON P.id = O.product_id";
        $linesWhere = "WHERE O.status = 'Active' AND Q.status = 'Active' AND Q.institution_id = $inst AND Q.branch_id = $branch
            AND Q.tran_date::date BETWEEN '$from' AND '$to'";
        $topProducts = DB::select("SELECT P.name, ROUND(CAST(SUM(O.qty * $price) AS NUMERIC), 2) AS revenue, SUM(COALESCE(O.base_qty, O.qty)) AS qty
            $linesFrom $linesWhere GROUP BY P.name ORDER BY revenue DESC LIMIT 8");

        $byCategory = DB::select("SELECT COALESCE(C.name, 'Uncategorised') AS category, ROUND(CAST(SUM(O.qty * $price) AS NUMERIC), 2) AS revenue
            $linesFrom LEFT JOIN product_categories C ON C.id = P.category_id $linesWhere GROUP BY 1 ORDER BY revenue DESC LIMIT 8");

        // ---- Operating expense breakdown (cost of sales is its own KPI) ----
        $expenseBreakdown = DB::select("SELECT A.description AS account, ROUND(CAST(SUM($signed) AS NUMERIC), 2) AS amount
            $glFrom AND A.acct_type = 'EXPENSE' AND H.acct_no NOT IN ('$gfs', '$rin')
            GROUP BY A.description HAVING ABS(SUM($signed)) >= 0.005 ORDER BY amount DESC LIMIT 8");

        // ---- Activity volume (counts per type in range) ----
        $volume = DB::select("SELECT
            (SELECT COUNT(id) FROM orders WHERE status='Active' AND institution_id=$inst AND branch_id=$branch AND tran_date::date BETWEEN '$from' AND '$to') AS sales,
            (SELECT COUNT(id) FROM stock_histories WHERE institution_id=$inst AND branch_id=$branch AND source_type IN ('OPENING', 'RESTOCK') AND stock_date::date BETWEEN '$from' AND '$to') AS purchases,
            (SELECT COUNT(id) FROM expenses WHERE institution_id=$inst AND branch_id=$branch AND expense_date::date BETWEEN '$from' AND '$to') AS expenses,
            (SELECT COUNT(id) FROM journal_entries WHERE institution_id=$inst AND branch_id=$branch AND journal_date::date BETWEEN '$from' AND '$to') AS journals,
            (SELECT COUNT(id) FROM production_orders WHERE institution_id=$inst AND branch_id=$branch AND created_on::date BETWEEN '$from' AND '$to') AS production,
            (SELECT COUNT(id) FROM stock_adjustments WHERE institution_id=$inst AND branch_id=$branch AND adjustment_date::date BETWEEN '$from' AND '$to') AS adjustments,
            (SELECT COUNT(id) FROM warehouse_transfers WHERE institution_id=$inst AND branch_id=$branch AND transfer_date::date BETWEEN '$from' AND '$to') AS transfers
        ")[0];

        return $this->genericResponse(true, 'Analytics fetched successfully', 200, [
            'range' => ['from' => $from, 'to' => $to, 'grain' => $grain],
            'kpis' => $kpis,
            'series' => $series,
            'top_products' => $topProducts,
            'by_category' => $byCategory,
            'expense_breakdown' => $expenseBreakdown,
            'activity_volume' => $volume,
        ], 'getAnalytics', $request);
    }

    /**
     * Unified, windowed calendar feed: read-only store activities plus the user's
     * own editable events/reminders, within [from, to] and the current branch.
     */
    public function getCalendarFeed(Request $request)
    {
        $userData = $this->getUserLogin();
        $inst = (int) $userData->institution_id;
        $branch = (int) $userData->branch_id;
        $from = $this->dateOr($request->from, date('Y-m-01'));
        $to = $this->dateOr($request->to, date('Y-m-t'));
        $types = is_array($request->types) ? $request->types : null;
        $want = fn ($t) => $types === null || in_array($t, $types);

        $events = [];
        $push = function ($rows, $type, $color, $route, $editable = false) use (&$events) {
            foreach ($rows as $r) {
                $events[] = [
                    'id' => $r->id ?? null,
                    'type' => $type,
                    'title' => $r->title,
                    'start' => $r->start,
                    'end' => $r->end ?? $r->start,
                    'amount' => isset($r->amount) ? (float) $r->amount : null,
                    'ref' => $r->ref ?? null,
                    'color' => $color,
                    'editable' => $editable,
                    'route' => $route,
                ];
            }
        };

        if ($want('sale')) {
            $push(DB::select("SELECT id, to_char(tran_date,'YYYY-MM-DD HH24:MI') AS start, 'Sale '||ref_no AS title, total AS amount, ref_no AS ref
                FROM orders WHERE status='Active' AND institution_id=$inst AND branch_id=$branch AND tran_date::date BETWEEN '$from' AND '$to'"), 'sale', '#22c55e', '/view-sales');
        }
        if ($want('purchase')) {
            $push(DB::select("SELECT H.id, to_char(H.stock_date,'YYYY-MM-DD HH24:MI') AS start, 'Restock '||COALESCE(P.name,'') AS title, (H.quantity*H.purchase_price) AS amount, P.ref_no AS ref
                FROM stock_histories H LEFT JOIN products P ON P.id=H.product_id
                WHERE H.institution_id=$inst AND H.branch_id=$branch AND H.stock_date::date BETWEEN '$from' AND '$to'"), 'purchase', '#3b82f6', '/view-products');
        }
        if ($want('expense')) {
            $push(DB::select("SELECT id, to_char(expense_date,'YYYY-MM-DD HH24:MI') AS start, 'Expense '||COALESCE(payee,'') AS title, amount, ref_no AS ref
                FROM expenses WHERE institution_id=$inst AND branch_id=$branch AND expense_date::date BETWEEN '$from' AND '$to'"), 'expense', '#ef4444', '/expenses');
        }
        if ($want('journal')) {
            $push(DB::select("SELECT id, to_char(journal_date,'YYYY-MM-DD HH24:MI') AS start, 'Journal '||ref_no AS title, total_amount AS amount, ref_no AS ref
                FROM journal_entries WHERE institution_id=$inst AND branch_id=$branch AND journal_date::date BETWEEN '$from' AND '$to'"), 'journal', '#a855f7', '/journal-entries');
        }
        if ($want('production')) {
            $push(DB::select("SELECT id, to_char(COALESCE(completed_on,started_on,scheduled_date,created_on),'YYYY-MM-DD HH24:MI') AS start,
                'Production '||order_no||' ('||status||')' AS title, wip_cost AS amount, order_no AS ref
                FROM production_orders WHERE institution_id=$inst AND branch_id=$branch AND COALESCE(completed_on,started_on,scheduled_date,created_on)::date BETWEEN '$from' AND '$to'"), 'production', '#f97316', '/production-orders');
        }
        if ($want('adjustment')) {
            $push(DB::select("SELECT id, to_char(adjustment_date,'YYYY-MM-DD HH24:MI') AS start, 'Adjustment '||type||' '||ref_no AS title, total_cost AS amount, ref_no AS ref
                FROM stock_adjustments WHERE institution_id=$inst AND branch_id=$branch AND adjustment_date::date BETWEEN '$from' AND '$to'"), 'adjustment', '#eab308', '/stock-adjustments');
        }
        if ($want('transfer')) {
            $push(DB::select("SELECT id, to_char(transfer_date,'YYYY-MM-DD HH24:MI') AS start, 'Transfer '||COALESCE(ref_no,'') AS title, quantity AS amount, ref_no AS ref
                FROM warehouse_transfers WHERE institution_id=$inst AND branch_id=$branch AND transfer_date::date BETWEEN '$from' AND '$to'"), 'transfer', '#14b8a6', '/warehouse-transfers');
        }
        if ($want('asset')) {
            $push(DB::select("SELECT id, to_char(purchase_date,'YYYY-MM-DD HH24:MI') AS start, 'Asset: '||name AS title, purchase_amount AS amount, ref_no AS ref
                FROM fixed_assets WHERE institution_id=$inst AND branch_id=$branch AND purchase_date::date BETWEEN '$from' AND '$to'"), 'asset', '#64748b', '/asset-register');
            $push(DB::select("SELECT id, to_char(maintenance_date,'YYYY-MM-DD HH24:MI') AS start, 'Maintenance '||COALESCE(ref_no,'') AS title, cost AS amount, ref_no AS ref
                FROM asset_maintenances WHERE institution_id=$inst AND branch_id=$branch AND maintenance_date::date BETWEEN '$from' AND '$to'"), 'asset', '#64748b', '/asset-maintenance');
        }

        // user reminders/events (editable)
        $userEvents = CalendarEvent::where(['institution_id' => $inst, 'branch_id' => $branch, 'status' => 'Active'])
            ->whereBetween(DB::raw('start::date'), [$from, $to])->get();
        foreach ($userEvents as $e) {
            $events[] = [
                'id' => $e->id,
                'type' => 'reminder',
                'title' => $e->title,
                'start' => date('Y-m-d H:i', strtotime($e->start)),
                'end' => $e->end ? date('Y-m-d H:i', strtotime($e->end)) : date('Y-m-d H:i', strtotime($e->start)),
                'amount' => null,
                'ref' => null,
                'color' => $e->color ?: '#6366f1',
                'editable' => true,
                'route' => null,
                'description' => $e->description,
                'all_day' => (bool) $e->all_day,
            ];
        }

        return $this->genericResponse(true, 'Calendar feed fetched successfully', 200, $events, 'getCalendarFeed', $request);
    }

    public function createCalendarEvent(Request $request)
    {
        $userData = $this->getUserLogin();
        if (empty($request->title) || empty($request->start)) {
            return $this->genericResponse(false, 'A title and start date/time are required', 400, null, 'createCalendarEvent', $request);
        }
        $event = CalendarEvent::create([
            'title' => $request->title,
            'description' => $request->description,
            'start' => $request->start,
            'end' => $request->end ?: null,
            'all_day' => (bool) $request->all_day,
            'color' => $request->color ?: '#6366f1',
            'category' => $request->category,
            'user_id' => $userData->id,
            'institution_id' => $userData->institution_id,
            'branch_id' => $userData->branch_id,
            'status' => 'Active',
            'created_by' => $userData->id,
            'created_on' => now(),
        ]);
        return $this->genericResponse(true, 'Reminder created successfully', 201, $event, 'createCalendarEvent', $request);
    }

    public function updateCalendarEvent(Request $request)
    {
        $userData = $this->getUserLogin();
        $event = CalendarEvent::where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!$event) {
            return $this->genericResponse(false, 'Reminder not found', 400, null, 'updateCalendarEvent', $request);
        }
        $event->title = $request->title ?? $event->title;
        $event->description = $request->description ?? $event->description;
        $event->start = $request->start ?? $event->start;
        $event->end = $request->end ?: $event->end;
        $event->all_day = $request->has('all_day') ? (bool) $request->all_day : $event->all_day;
        $event->color = $request->color ?? $event->color;
        $event->updated_by = $userData->id;
        $event->updated_on = now();
        $event->save();
        return $this->genericResponse(true, 'Reminder updated successfully', 200, $event, 'updateCalendarEvent', $request);
    }

    public function deleteCalendarEvent(Request $request)
    {
        $userData = $this->getUserLogin();
        $event = CalendarEvent::where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!$event) {
            return $this->genericResponse(false, 'Reminder not found', 400, null, 'deleteCalendarEvent', $request);
        }
        $event->delete();
        return $this->genericResponse(true, 'Reminder deleted successfully', 200, null, 'deleteCalendarEvent', $request);
    }
}
