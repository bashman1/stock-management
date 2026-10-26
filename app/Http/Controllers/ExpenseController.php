<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\GlAccounts;
use App\Models\CntrlParameter;
use App\Models\Branch;
use App\Models\Payable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpenseController extends Controller
{
    // payParamCode(), resolveControlAccount() and postJournal() are now shared
    // protected helpers on the base Controller (reused by the Asset module too).

    public function createExpense(Request $request)
    {
        $userData = $this->getUserLogin();
        $branch = Branch::find($userData->branch_id);
        if (!isset($branch)) {
            return $this->genericResponse(false, 'Branch not found', 400, null, 'createExpense', $request);
        }

        $method = strtoupper((string) $request->payment_method);
        $amount = (float) $request->amount;
        if (empty($request->expense_acct_no) || $amount <= 0 || !in_array($method, ['CASH', 'BANK', 'CREDIT'])) {
            return $this->genericResponse(false, 'An expense account, a positive amount and a valid payment method are required', 400, null, 'createExpense', $request);
        }

        // Dr = the chosen expense account (must be an EXPENSE account in this branch)
        $expenseAcct = GlAccounts::where([
            'acct_no' => $request->expense_acct_no,
            'institution_id' => $userData->institution_id,
            'branch_id' => $userData->branch_id,
        ])->first();
        if (!isset($expenseAcct) || $expenseAcct->acct_type !== 'EXPENSE') {
            return $this->genericResponse(false, 'Invalid expense account', 400, null, 'createExpense', $request);
        }

        // Cr = cash / bank / accounts payable
        $payAcct = $this->resolveControlAccount($this->payParamCode($method), $userData->institution_id, $branch->code);
        if (!isset($payAcct)) {
            return $this->genericResponse(false, 'Payment account is not configured for this branch', 400, null, 'createExpense', $request);
        }

        $receiptPath = null;
        if (!empty($request->receipt)) {
            try {
                $receiptPath = $this->saveBase64Image($request->receipt, 'expenses/receipts');
            } catch (\Throwable $th) {
                $receiptPath = null;
            }
        }

        $expenseDate = $this->businessDate($request->expense_date ?? null);
        $desc = $request->description ?: ('Expense - ' . $expenseAcct->description);
        $refNo = 'EXP-' . strtoupper(substr(str_replace('-', '', $this->generateUuid()), 0, 8));

        DB::beginTransaction();
        try {
            $tranId = $this->generateUuid();
            $this->postJournal(
                $expenseAcct->acct_no,
                $expenseAcct->acct_type,
                $payAcct->acct_no,
                $payAcct->acct_type,
                $amount,
                $tranId,
                $desc,
                $expenseDate,
                $userData,
                'EXP',
                'Expense'
            );

            if ($method === 'CREDIT') {
                $this->createPayable((array) [
                    'ref_no' => $refNo,
                    'tran_id' => $tranId,
                    'supplier_id' => $request->supplier_id,
                    'amount' => $amount,
                    'amount_paid' => 0,
                    'status' => 'Active',
                    'institution_id' => $userData->institution_id,
                    'branch_id' => $userData->branch_id,
                    'created_by' => $userData->id,
                    'created_on' => now(),
                ]);
            }

            $expense = Expense::create([
                'ref_no' => $refNo,
                'expense_acct_no' => $expenseAcct->acct_no,
                'category_name' => $expenseAcct->description,
                'amount' => $amount,
                'payment_method' => $method,
                'pay_acct_no' => $payAcct->acct_no,
                'payee' => $request->payee,
                'supplier_id' => $request->supplier_id,
                'description' => $request->description,
                'expense_date' => $expenseDate,
                'receipt_path' => $receiptPath,
                'tran_id' => $tranId,
                'payment_status' => $method === 'CREDIT' ? 'Unpaid' : 'Paid',
                'status' => 'Active',
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'user_id' => $userData->id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ]);

            DB::commit();
            return $this->genericResponse(true, 'Expense recorded successfully', 201, $expense, 'createExpense', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'createExpense', $request);
        }
    }

    public function getExpenses(Request $request)
    {
        $userData = $this->getUserLogin();
        $q = Expense::where([
            'institution_id' => $userData->institution_id,
            'branch_id' => $userData->branch_id,
            'status' => 'Active',
        ]);
        if (!empty($request->from_date)) {
            $q->whereDate('expense_date', '>=', $request->from_date);
        }
        if (!empty($request->to_date)) {
            $q->whereDate('expense_date', '<=', $request->to_date);
        }
        if (!empty($request->payment_method)) {
            $q->where('payment_method', strtoupper($request->payment_method));
        }
        if (!empty($request->category)) {
            $q->where('expense_acct_no', $request->category);
        }
        if (!empty($request->searchKey)) {
            $s = $request->searchKey;
            $q->where(function ($w) use ($s) {
                $w->where('category_name', 'ILIKE', "%$s%")
                    ->orWhere('payee', 'ILIKE', "%$s%")
                    ->orWhere('description', 'ILIKE', "%$s%")
                    ->orWhere('ref_no', 'ILIKE', "%$s%");
            });
        }
        $expenses = $q->orderBy('expense_date', 'desc')->orderBy('id', 'desc')->get();
        return $this->genericResponse(true, 'Expenses fetched successfully', 200, $expenses, 'getExpenses', $request);
    }

    public function payExpense(Request $request)
    {
        $userData = $this->getUserLogin();
        $branch = Branch::find($userData->branch_id);
        if (!isset($branch)) {
            return $this->genericResponse(false, 'Branch not found', 400, null, 'payExpense', $request);
        }

        $expense = Expense::where([
            'id' => $request->id,
            'institution_id' => $userData->institution_id,
            'branch_id' => $userData->branch_id,
        ])->first();
        if (!isset($expense)) {
            return $this->genericResponse(false, 'Expense not found', 400, null, 'payExpense', $request);
        }
        if ($expense->payment_status === 'Paid') {
            return $this->genericResponse(false, 'Expense is already paid', 400, $expense, 'payExpense', $request);
        }

        $method = strtoupper((string) ($request->payment_method ?? 'CASH'));
        if (!in_array($method, ['CASH', 'BANK'])) {
            $method = 'CASH';
        }

        // Dr Accounts Payable / Cr Cash|Bank
        $apAcct = $this->resolveControlAccount('AP', $userData->institution_id, $branch->code);
        $payAcct = $this->resolveControlAccount($this->payParamCode($method), $userData->institution_id, $branch->code);
        if (!isset($apAcct) || !isset($payAcct)) {
            return $this->genericResponse(false, 'Payable / payment account not configured', 400, null, 'payExpense', $request);
        }

        DB::beginTransaction();
        try {
            $tranId = $this->generateUuid();
            $this->postJournal(
                $apAcct->acct_no,
                $apAcct->acct_type,
                $payAcct->acct_no,
                $payAcct->acct_type,
                (float) $expense->amount,
                $tranId,
                'Settle expense ' . $expense->ref_no,
                now(),
                $userData,
                'EXP',
                'Expense Payment'
            );

            $expense->payment_status = 'Paid';
            $expense->paid_tran_id = $tranId;
            $expense->paid_on = now();
            $expense->updated_by = $userData->id;
            $expense->updated_on = now();
            $expense->save();

            // settle the payable raised when the credit expense was recorded
            $payable = Payable::where('tran_id', $expense->tran_id)->first();
            if (isset($payable)) {
                $payable->amount_paid = (float) $payable->amount;
                $payable->status = 'Settled';
                $payable->updated_by = $userData->id;
                $payable->updated_on = now();
                $payable->save();
            }

            DB::commit();
            return $this->genericResponse(true, 'Expense paid successfully', 200, $expense, 'payExpense', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'payExpense', $request);
        }
    }
}
