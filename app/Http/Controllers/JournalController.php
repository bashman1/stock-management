<?php

namespace App\Http\Controllers;

use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\GlAccounts;
use App\Models\GlBalances;
use App\Models\GlHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalController extends Controller
{
    /**
     * Post a multi-line general journal: many Dr/Cr lines that must balance.
     * Stores a voucher (header + lines) and posts each line to the GL under one tran_id.
     */
    public function createJournalEntry(Request $request)
    {
        $userData = $this->getUserLogin();
        $lines = $request->lines;

        if (!is_array($lines) || count($lines) < 2) {
            return $this->genericResponse(false, 'A journal needs at least two lines', 400, null, 'createJournalEntry', $request);
        }

        $totalDr = 0;
        $totalCr = 0;
        $resolved = [];
        foreach ($lines as $ln) {
            $acctNo = $ln['acct_no'] ?? null;
            $drCr = ($ln['dr_cr'] ?? '') === 'Cr' ? 'Cr' : (($ln['dr_cr'] ?? '') === 'Dr' ? 'Dr' : null);
            $amount = (float) ($ln['amount'] ?? 0);
            if (empty($acctNo) || !$drCr || $amount <= 0) {
                return $this->genericResponse(false, 'Each line needs an account, a side (Dr/Cr) and a positive amount', 400, null, 'createJournalEntry', $request);
            }
            $acct = GlAccounts::where([
                'acct_no' => $acctNo,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
            ])->first();
            if (!isset($acct)) {
                return $this->genericResponse(false, "Account $acctNo not found in this branch", 400, null, 'createJournalEntry', $request);
            }
            $resolved[] = ['acct' => $acct, 'dr_cr' => $drCr, 'amount' => $amount, 'description' => $ln['description'] ?? null];
            if ($drCr === 'Dr') {
                $totalDr += $amount;
            } else {
                $totalCr += $amount;
            }
        }

        if ($totalDr <= 0 || abs($totalDr - $totalCr) > 0.01) {
            return $this->genericResponse(false, 'Total debits must equal total credits', 400, ['totalDr' => $totalDr, 'totalCr' => $totalCr], 'createJournalEntry', $request);
        }

        $journalDate = $request->journal_date ?? now();
        $narration = $request->narration;
        $refNo = 'JV-' . strtoupper(substr(str_replace('-', '', $this->generateUuid()), 0, 8));

        DB::beginTransaction();
        try {
            $tranId = $this->generateUuid();

            // header transaction record
            $first = $resolved[0]['acct'];
            $this->postTransaction((array) [
                'acct_no' => $first->acct_no,
                'acct_type' => $first->acct_type,
                'contra_acct_no' => null,
                'contra_acct_type' => null,
                'description' => $narration ?: 'Journal entry',
                'dr_cr_ind' => 'DR/CR',
                'tran_amount' => $totalDr,
                'reversal_flag' => 'N',
                'tran_date' => $journalDate,
                'tran_cd' => 'JNL',
                'tran_id' => $tranId,
                'status' => 'Active',
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
                'ext_ref' => $refNo,
                'tran' => 'Journal Entry',
            ]);

            $entry = JournalEntry::create([
                'ref_no' => $refNo,
                'journal_date' => $journalDate,
                'narration' => $narration,
                'total_amount' => $totalDr,
                'tran_id' => $tranId,
                'status' => 'Active',
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ]);

            $base = [
                'reversal_flag' => 'N',
                'transaction_date' => $journalDate,
                'tran_type' => 'Journal Entry',
                'tran_cd' => 'JNL',
                'tran_id' => $tranId,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'status' => 'Active',
                'contra_acct_no' => null,
                'contra_acct_type' => null,
            ];

            foreach ($resolved as $r) {
                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'acct_no' => $r['acct']->acct_no,
                    'acct_type' => $r['acct']->acct_type,
                    'dr_cr' => $r['dr_cr'],
                    'amount' => $r['amount'],
                    'description' => $r['description'],
                    'institution_id' => $userData->institution_id,
                    'branch_id' => $userData->branch_id,
                    'created_by' => $userData->id,
                    'created_on' => now(),
                ]);

                $data = (object) array_merge($base, [
                    'acct_no' => $r['acct']->acct_no,
                    'acct_type' => $r['acct']->acct_type,
                    'tran_amt' => $r['amount'],
                    'description' => $r['description'] ?: ($narration ?: 'Journal entry'),
                ]);
                if ($r['dr_cr'] === 'Dr') {
                    $this->postGlDR($data);
                } else {
                    $this->postGlCR($data);
                }
            }

            DB::commit();
            return $this->genericResponse(true, 'Journal entry posted successfully', 201, $entry, 'createJournalEntry', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'createJournalEntry', $request);
        }
    }

    public function getJournalEntries(Request $request)
    {
        $userData = $this->getUserLogin();
        $q = JournalEntry::withCount('lines')
            ->where(['institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id]);
        if (!empty($request->from_date)) {
            $q->whereDate('journal_date', '>=', $request->from_date);
        }
        if (!empty($request->to_date)) {
            $q->whereDate('journal_date', '<=', $request->to_date);
        }
        if (!empty($request->searchKey)) {
            $s = $request->searchKey;
            $q->where(function ($w) use ($s) {
                $w->where('ref_no', 'ILIKE', "%$s%")->orWhere('narration', 'ILIKE', "%$s%");
            });
        }
        $entries = $q->orderBy('id', 'desc')->get();
        return $this->genericResponse(true, 'Journal entries fetched successfully', 200, $entries, 'getJournalEntries', $request);
    }

    public function getJournalEntry(Request $request)
    {
        $userData = $this->getUserLogin();
        $entry = JournalEntry::with('lines')
            ->where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!isset($entry)) {
            return $this->genericResponse(false, 'Journal entry not found', 400, null, 'getJournalEntry', $request);
        }
        // attach account descriptions to the lines
        $acctNos = $entry->lines->pluck('acct_no')->all();
        $names = GlAccounts::whereIn('acct_no', $acctNos)->pluck('description', 'acct_no');
        $entry->lines->transform(function ($l) use ($names) {
            $l->account_name = $names[$l->acct_no] ?? null;
            return $l;
        });
        return $this->genericResponse(true, 'Journal entry fetched successfully', 200, $entry, 'getJournalEntry', $request);
    }

    /**
     * Reverse (void) a journal: back out each posted line's balance effect and
     * mark its gl_histories rows reversed. Flag-and-exclude (no new rows), so
     * the reports that filter reversal_flag='N' ignore it.
     */
    public function reverseJournalEntry(Request $request)
    {
        $userData = $this->getUserLogin();
        $entry = JournalEntry::where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!isset($entry)) {
            return $this->genericResponse(false, 'Journal entry not found', 400, null, 'reverseJournalEntry', $request);
        }
        if ($entry->status === 'Reversed') {
            return $this->genericResponse(false, 'Journal entry is already reversed', 400, $entry, 'reverseJournalEntry', $request);
        }

        DB::beginTransaction();
        try {
            $rows = GlHistory::where('tran_id', $entry->tran_id)->where('reversal_flag', 'N')->get();
            foreach ($rows as $item) {
                $glAccount = GlAccounts::where('acct_no', $item->acct_no)->first();
                $acctType = $glAccount?->acct_type;
                $balance = GlBalances::where('acct_no', $item->acct_no)->first();
                if (isset($balance)) {
                    $isDebitNormal = ($acctType == 'ASSET' || $acctType == 'EXPENSE');
                    $amt = (float) $item->tran_amount;
                    if ($item->dr_cr_ind == 'Dr') {
                        $balance->balance = $isDebitNormal ? $balance->balance - $amt : $balance->balance + $amt;
                    } else {
                        $balance->balance = $isDebitNormal ? $balance->balance + $amt : $balance->balance - $amt;
                    }
                    $balance->updated_on = now();
                    $balance->save();
                }
                $item->status = 'Deleted';
                $item->reversal_flag = 'Y';
                $item->updated_by = $userData->id;
                $item->updated_on = now();
                $item->save();
            }

            $entry->status = 'Reversed';
            $entry->reversed_tran_id = $entry->tran_id;
            $entry->reversed_on = now();
            $entry->reversed_by = $userData->id;
            $entry->updated_by = $userData->id;
            $entry->updated_on = now();
            $entry->save();

            DB::commit();
            return $this->genericResponse(true, 'Journal entry reversed successfully', 200, $entry, 'reverseJournalEntry', $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, null, 'reverseJournalEntry', $request);
        }
    }
}
