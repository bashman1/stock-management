Great question — this is the structural fix that prevents the corrupt half-posted sales from recurring. The root weakness today is that every GL entry is posted by independent postGlDR / postGlCR calls, each opening its own DB::beginTransaction()/commit() (Controller.php:592, 635), with nothing asserting that debits = credits for a transaction. So if a sale posts leg 1 and then crashes on leg 2, leg 1 is already committed → a single-leg entry.

Here are the measures, strongest first:

1. Post each transaction as one balanced journal, not loose legs (highest impact)
Replace the scattered postGlDR/postGlCR pairs with a single helper:


postJournal(string $tranId, array $legs)  // each leg: acct_no, dr_cr, amount
It (a) looks up acct_type from gl_accounts, (b) asserts Σdebits === Σcredits (within epsilon) and throws UnbalancedJournalException if not, (c) writes all history rows + balance updates, (d) all inside one DB transaction. A transaction is now all-or-nothing and can never be unbalanced — single-leg entries become structurally impossible. newSale, createNewProduct, restock, expenses, rent, etc. all build a $legs array and call it once.

2. True atomicity per business operation
Remove the inner DB::beginTransaction()/commit() from postGlDR/postGlCR (or fold them into #1) so the caller's transaction is the only one. Today the inner commits mean a leg can persist even when the overall operation later fails. One transaction wrapping the whole sale/stock-in → any failure rolls back every leg.

3. Database-level guard (defense in depth)
A Postgres deferred constraint trigger on gl_histories that, at COMMIT, checks SUM(dr) = SUM(cr) per tran_id. Even a future bug or a manual SQL insert can't leave the ledger unbalanced — the commit is rejected. This is the belt-and-suspenders backstop behind the app logic.

4. Continuous monitoring (catch drift early)
Schedule the gl:reconcile command I built as a daily dry-run that flags any branch where debits ≠ credits (or any unbalanced tran_id) and alerts you — so problems surface in a day, not after 4M of accumulation. Reuses ReconcileGlBalances + its findUnbalanced().

5. Tests for every posting flow
A feature test per money flow (sale, stock-in, restock, reversal, expense, rent) asserting the resulting gl_histories for that tran_id net to zero. Locks the invariant in CI so refactors can't regress it.

6. (Supporting) require a real acct_type on history
Add an acct_type column to gl_histories (it has none today — the very gap that caused the reversal bug). Then postings, reversals, and reports never have to re-derive it or risk a null.

My recommendation: do #1 + #2 together (they're the real cure — they make single-leg entries impossible at the application layer), add #3 as the database backstop, and turn on #4 so you'd know within a day if anything ever slips. #5 and #6 harden it further.

Want me to implement #1+#2 (the balanced-journal helper and atomic wrapping across the sale/stock-in/reversal paths) and add the #3 trigger? That's the combination that actually guarantees "no single-leg transactions." I'd do it in plan mode first since it touches every posting path.
