Here's the investigation summary:

## Bugs Found (7 total)

| # | Bug | Severity | Location |
|---|-----|----------|----------|
| 1 | `createBranchGlAccounts()` — condition `!empty($checkExistingGlAcct)` is always false for Laravel collections, so GL accounts are **never created** | CRITICAL | `Controller.php:246` |
| 2 | `restock()` — overwrites `$stock` with GlAccounts object then uses `$stock->id` as `stock_id` — **wrong ID** in GL transactions | HIGH | `ProductController.php:446,476` |
| 3 | `restock()` — no null check after fetching stock; crashes if no stock record exists | HIGH | `ProductController.php:380` |
| 4 | `Product::stock()` — `belongsTo(stock::class, 'id', 'product_id')` should be `hasOne(stock::class, 'product_id', 'id')` — **inverted relationship** | MEDIUM | `Product.php:41` |
| 5 | `OrderItem::stock()` — references `Stock::class` (capital S) but model is `stock` (lowercase) — **class not found** | MEDIUM | `OrderItem.php:26` |
| 6 | `getChartOfAccounts()` — `INNER JOIN gl_balances` hides accounts without balances yet | LOW | `GlAccountsController.php:50` |
| 7 | `getBalanceSheet()` — hardcodes `gl_no <> '1305001' AND gl_no <> '1307001'` instead of using account classification | LOW | `GlAccountsController.php:619` |

## Chart of Accounts

**Completed:** GL Cats → SubCats → Types → Accounts, GL Generate sequences, Control Parameters (CL/CGL/STI/SL/PIA/PIL/PP etc.), GL Balances + History posting, Income Statement, Balance Sheet, Cash Book, VAT Payable.

**Pending:** No fiscal year-end closure, no multi-level hierarchy in reports, two duplicate empty migrations (`gl_accounts`/`gl_balances` from 2024-02-10 vs 2024-03-06), hardcoded balance sheet exclusions.

## Restocking Redesign

**Current:** Single `stocks` record per product/branch. Restock overwrites prices and adds quantity — **losses price history**.

**Recommended:** Create `stock_lots` table where each restock creates a **new lot** with its own `purchase_price` and `selling_price`. Sales deduct via FIFO across lots. This is a cleaner, auditable approach.

The full plan with detailed migration steps is saved at `.kilo/plans/1781636042788-glowing-meadow.md`.
