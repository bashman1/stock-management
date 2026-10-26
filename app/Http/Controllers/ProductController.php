<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\stock;
use App\Models\CntrlParameter;
use App\Models\stockHistory;
use App\Models\Branch;
use App\Models\TempMember;
use App\Models\TempProduct;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\GlAccounts;
use App\Models\GlHistory;
use App\Models\Institution;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Transaction;
use Carbon\Carbon;
use App\Services\EfrisApiService;
use App\Models\EfrisMeasurementUnits;
use Illuminate\Support\Arr;
use App\Models\measurementUnit;
use App\Models\ProductUnit;
use App\Models\ProductType;
use App\Models\ProductComponent;
use App\Models\ProductCategory;
use App\Models\EfrisComodityCategory;


class ProductController extends Controller
{

    protected EfrisApiService $efrisService;

    public function __construct(EfrisApiService $efrisService)
    {
        $this->efrisService = $efrisService;
    }
    public function createProduct(Request $request)
    {
        $response = $this->createNewProduct($request);

        if (isset($response->error) && $response->error) {
            return $this->genericResponse(false, $response->message, 400, null, "createProduct", $request);
        }

        return $this->genericResponse(true, $response->message, 201, $response->product, "createProduct", $request);
    }


    public function createNewProduct($request)
    {
        $userData = $this->getUserLogin();
        $product = null;
        $institutionData = $this->getUserInstitution();

        $institution = Institution::find($userData->institution_id);

        $supplier = Supplier::find($request->supplier_id);

        $isEdit = $request->id;
        $refNo = null;
        $message = null;

        if (isset($isEdit)) {
            $product = Product::find($isEdit);
            $product->updated_by = $userData->id;
            $product->updated_on = Carbon::now();
            $refNo = $product->ref_no;
            $message = "Product updated successfully";
            // return $this->genericResponse(false,"testing editing", 400, $product);
        } else {
            $product = new Product();
            $product->created_by = $userData->id;
            $product->created_on = Carbon::now();
            $refNo = $this->generateRefNumber('product_ref');
            $message = "Product Created successfully";
        }

        // --- Multiple units of measure -------------------------------------
        // When the client sends a `units` array, the base unit is the single
        // source of truth for the product's measurement_unit_id and for the
        // stock/GL/EFRIS prices. Validate before touching the database.
        $units = $request->units ?? null;
        $baseUnit = null;
        if (is_array($units) && count($units) > 0) {
            $unitsError = $this->validateProductUnits($units);
            if ($unitsError !== true) {
                return (object)['message' => $unitsError, 'product' => null, 'error' => true];
            }
            foreach ($units as $u) {
                if (!empty($u['is_base'])) {
                    $baseUnit = $u;
                    break;
                }
            }
            if ($request instanceof Request) {
                $request->merge([
                    'measurement_unit_id' => $baseUnit['measurement_unit_id'],
                    'purchase_price'      => $baseUnit['purchase_price'],
                    'selling_price'       => $baseUnit['selling_price'],
                ]);
            } else {
                $request->measurement_unit_id = $baseUnit['measurement_unit_id'];
                $request->purchase_price      = $baseUnit['purchase_price'];
                $request->selling_price       = $baseUnit['selling_price'];
            }
        }

        $categoryId = $request->category_id;
        $unitId = $request->measurement_unit_id;
        if ($institutionData->efris_enabled) {
            $efrisCat = $this->createOrReturnProductCategories($categoryId);
            $categoryId = $efrisCat->id;
            $efrisUnit = $this->createOrReturnMeasurementUnit($unitId);
            $unitId = $efrisUnit->id;
        }


        DB::beginTransaction();
        $product->name = $request->name;
        $product->product_no = $request->product_no;
        $product->category_id = $categoryId;
        $product->sub_category_id = $request->sub_category_id;
        $product->manufacturer_id = $request->manufacturer_id;
        $product->supplier_id = $request->supplier_id;
        $product->measurement_unit_id = $unitId;
        $product->description = $request->description;
        $product->institution_id = $userData->institution_id;
        $product->user_id = $userData->id;
        $product->status = $userData->status;
        $product->type_id = $request->type_id;
        $product->gauge_id = $request->gauge_id;
        $product->ref_no = $refNo;
        $product->secondary_measurement_unit_id = $request->secondary_measurement_unit_id;
        $product->secondary_weight = (float) $request->secondary_weight;
        $product->secondary_price = (float) $request->secondary_price;
        if (isset($institution->is_tax_enabled) && $institution->is_tax_enabled) {
            $product->tax_config = $request->tax_config;
        }

        // Resolve the product's behaviour from its type (defaults to STOCK so
        // legacy products are unchanged). `nature` drives the stock + GL branching
        // below: only STOCK items track inventory and post inventory/COGS.
        $nature = 'STOCK';
        if (!empty($request->type_id)) {
            $pt = ProductType::find($request->type_id);
            if ($pt && !empty($pt->nature)) {
                $nature = $pt->nature;
            }
        }
        $product->nature = $nature;
        $product->tracks_stock = ($nature === 'STOCK');
        $product->valuation_method = in_array($request->valuation_method, ['FIFO', 'LIFO', 'WAVG', 'STANDARD'])
            ? $request->valuation_method : 'WAVG';
        $product->standard_cost = (float) ($request->standard_cost ?? 0);
        $product->save();

        // persist the product's units of measure ($unitId is the base unit,
        // possibly remapped above when EFRIS is enabled)
        if (is_array($units) && count($units) > 0) {
            $this->syncProductUnits($product, $units, $unitId, $userData, $institutionData->efris_enabled);
        }

        // BUNDLE: persist the component products that get decremented on sale
        if ($nature === 'BUNDLE') {
            $this->syncBundleComponents($product, $request->components ?? [], $userData);
        }


        if (!isset($isEdit) && $nature === 'STOCK') {
            $branch = Branch::where(['id' => $userData->branch_id, 'institution_id' => $userData->institution_id])->first();

            // Opening stock: Dr Inventories / Cr the payment account. Stock entered
            // with a new product is the owner's stock unless it was paid for now, so
            // a missing method means OPENING (Opening Balance Equity), not cash.
            $paymentMethod = $request->payment_method ?? 'OPENING';
            $postTran = $this->postStockReceipt($userData, $branch, $product,
                (float) $request->quantity * (float) $request->purchase_price, $paymentMethod,
                $request->date, 'Create Product', $refNo);

            if ($paymentMethod == "CREDIT" && $postTran) {
                $payable = (array)[
                    "ref_no" => $refNo,
                    "tran_id" => $postTran->tran_id,
                    "supplier_id" => $request->supplier_id,
                    "product_id" => $product->id,
                    "manufacturer_id" => $request->manufacturer_id,
                    "amount" => (float) $postTran->tran_amount,
                    "amount_paid" => 0,
                    "status" => "Active",
                    "institution_id" => $userData->institution_id,
                    "branch_id" => $userData->branch_id,
                    "created_by" => $userData->id,
                    "created_on" => Carbon::now(),
                ];
                $this->createPayable($payable);
            }


            if (isset($institutionData->is_tax_enabled) && $institutionData->efris_enabled) {
                // creating product to efris

                // $unit = measurementUnit::find($product->measurement_unit_id);
                // $efrisUnit = EfrisMeasurementUnits::where(['value' => $unit->efris_code])->first();

                $efrisUnit = $this->getEfrisMeasurementUnitByUnitId($product->measurement_unit_id);
                // $cat = ProductCategory::find($product->category_id);
                // $efrisCat = EfrisComodityCategory::where(['commodity_category_code' => $cat->efris_code])->first();

               $efrisCat = $this->getEfrisCommodityCatByProdCatId($product->category_id);

               $this->efrisCreateProduct($request, $refNo, $efrisUnit->value, $efrisCat->commodity_category_code, $efrisCat->excisable);

                // efris adding stock

                $this->efrisCreateOpeningStock($supplier?->name ?? '', $request->date, $refNo, $request->quantity, $request->selling_price, $efrisUnit->value);
            }
        }

        // Non-stock items are expensed at purchase (no inventory asset):
        // Dr COGS-expense / Cr Cash|AP for the opening quantity x cost.
        if (!isset($isEdit) && $nature === 'NON_STOCK'
            && (float) $request->quantity > 0 && (float) $request->purchase_price > 0) {
            $this->postNonStockPurchase($request, $product, $refNo, $userData);
        }

        $stock = null;
        $history = null;
        if (isset($isEdit)) {
            // the editor's own branch row: a product has one stocks row per branch,
            // and an unscoped first() overwrote another branch's price/quantity
            $stock = stock::where(['product_id' => $isEdit, 'branch_id' => $userData->branch_id])->first();
            if (!isset($stock)) {
                $stock = new stock();
                $stock->created_by = $userData->id;
                $stock->created_on = Carbon::now();
            }
            $stock->updated_by = $userData->id;
            $stock->updated_on = Carbon::now();

            // may legitimately be null (32 stock rows have no history row at all);
            // assigning onto null used to fatal here
            $history = stockHistory::where(['product_id' => $isEdit, 'stock_id' => $stock->id])->orderBy('created_at', 'desc')->first();


            // increase or decrease stock in efris
            if (isset($institutionData->is_tax_enabled) && $institutionData->efris_enabled) {

                if ($stock->quantity > $request->quantity) {
                    // decrease stock in efris
                    $difference = $stock->quantity - $request->quantity;
                    $this->decreaseStock($product->ref_no, $difference, $request->selling_price);
                } else if ($stock->quantity < $request->quantity) {
                    // increase stock in efris
                     $efrisUnit = $this->getEfrisMeasurementUnitByUnitId($product->measurement_unit_id);
                    $difference = $request->quantity - $stock->quantity;
                    $this->increaseStock($supplier?->name ?? '', $request->date, $product->ref_no, $difference, $efrisUnit->value,$request->selling_price);
                } else {
                    // no change
                }
            }
        } else {
            $stock = new stock();
            $stock->created_by = $userData->id;
            $stock->created_on = Carbon::now();

            $history = new stockHistory();
            $history->created_by = $userData->id;
            $history->created_on = Carbon::now();
        }

        // warehouse ledger follows the change in branch stock quantity
        $oldQty = (float) ($stock->quantity ?? 0);
        $oldUnitCost = (float) ($stock->purchase_price ?? 0);
        $stock->purchase_price =  (float) $request->purchase_price;
        $stock->selling_price = (float) $request->selling_price;
        $stock->discount = $request->discount;
        $stock->product_id = $product->id;
        $stock->quantity = (float) $request->quantity;
        $stock->min_quantity = (float) $request->min_quantity;
        $stock->max_quantity = (float) $request->max_quantity;
        $stock->institution_id = $userData->institution_id;
        $stock->stock_date = $this->businessDate($request->date);
        $stock->manufactured_date = $request->manufactured_date;
        $stock->expiry_date = $request->expiry_date;
        $stock->payment_method = $request->payment_method;
        $stock->branch_id = $userData->branch_id;
        $stock->user_id = $userData->id;
        $stock->status = $request->status;
        $stock->save();

        // mirror the delta into the branch's default warehouse sub-ledger
        $this->adjustWarehouseStock($product->id, $userData->branch_id, ((float) $request->quantity) - $oldQty);

        // An edit is a stock count / cost correction, never a rewrite of the original
        // receipt: move the cost layers to the new quantity and cost and post the
        // value difference against Stock Adjustment. (The old code overwrote the
        // opening posting with on-hand qty x price, ignoring everything sold since.)
        if (isset($isEdit) && $nature === 'STOCK') {
            $editBranch = Branch::where(['id' => $userData->branch_id, 'institution_id' => $userData->institution_id])->first();
            $this->adjustStockToCount($product, $editBranch, $userData, (float) $request->quantity,
                (float) $request->purchase_price, $oldUnitCost, $product->ref_no);
        }

        // record opening stock as a cost layer (new STOCK products only); $branch and
        // $postTran were resolved in the GL block above under the same condition
        if (!isset($isEdit) && $nature === 'STOCK' && (float) $request->quantity > 0) {
            $variance = $this->receiveStock($product->id, $userData->branch_id, (float) $request->quantity, (float) $request->purchase_price, 'OPENING', $refNo, $this->businessDate($request->date), $userData, $postTran->tran_id ?? null);
            $this->postStandardVariance($userData, $branch, $variance, $postTran->tran_id ?? null, "Opening stock variance for $product->name");
        }

        // --- stock_histories is an append-only movement ledger -------------------
        // Every row is a CHANGE in quantity, never a snapshot: opening stock on
        // create, the received quantity in restock(), and the difference here when
        // an edit changes quantity. Editing used to overwrite the newest row with
        // the new absolute quantity, which destroyed the movement and left the
        // stock statement unable to reconcile against stock on hand.
        $qtyDelta = (float) $request->quantity - $oldQty;

        $fillHistory = function ($h, $qty, $sourceType) use ($request, $product, $stock, $userData) {
            $h->purchase_price = (float) $request->purchase_price;
            $h->selling_price = (float) $request->selling_price;
            $h->discount = $request->discount;
            $h->product_id = $product->id;
            $h->stock_id = $stock->id;
            $h->quantity = $qty;
            $h->min_quantity = (float) $request->min_quantity;
            $h->max_quantity = (float) $request->max_quantity;
            $h->institution_id = $userData->institution_id;
            $h->stock_date = $this->businessDate($request->date);
            $h->manufactured_date = $request->manufactured_date;
            $h->expiry_date = $request->expiry_date;
            $h->payment_method = $request->payment_method;
            $h->branch_id = $userData->branch_id;
            $h->user_id = $userData->id;
            $h->status = $request->status;
            $h->source_type = $sourceType;
            $h->save();
        };

        if (!isset($isEdit)) {
            $fillHistory($history, (float) $request->quantity, 'OPENING');
        } elseif ($qtyDelta != 0.0) {
            // a correction, not a receipt — negative when the edit reduces stock
            $adjustment = new stockHistory();
            $adjustment->created_by = $userData->id;
            $adjustment->created_on = Carbon::now();
            $fillHistory($adjustment, $qtyDelta, 'ADJUSTMENT');
        } elseif (isset($history)) {
            // quantity unchanged: refresh the prices on the latest movement, but
            // leave its quantity alone so the ledger keeps its history
            $history->purchase_price = (float) $request->purchase_price;
            $history->selling_price = (float) $request->selling_price;
            $history->discount = $request->discount;
            $history->updated_by = $userData->id;
            $history->updated_on = Carbon::now();
            $history->save();
        }

        DB::commit();
        return  (object)['message' => $message, 'product' => $product];
    }

    /**
     * Replace a bundle product's component list. Components are decremented from
     * their own stock when the bundle sells (see OrderController::newSale).
     */
    private function syncBundleComponents($product, $components, $userData)
    {
        ProductComponent::where('bundle_product_id', $product->id)->delete();
        foreach ((array) $components as $c) {
            $compId = $c['component_product_id'] ?? $c['id'] ?? null;
            if (empty($compId)) {
                continue;
            }
            ProductComponent::create([
                'bundle_product_id'    => $product->id,
                'component_product_id' => $compId,
                'quantity'             => (float) ($c['quantity'] ?? 1),
                'measurement_unit_id'  => $c['measurement_unit_id'] ?? null,
                'institution_id'       => $userData->institution_id,
                'branch_id'            => $userData->branch_id,
                'created_by'           => $userData->id,
                'created_on'           => now(),
            ]);
        }
    }

    /**
     * Post the purchase of a NON_STOCK item: cost is expensed immediately rather
     * than capitalised as inventory. Dr COGS-expense (GFS) / Cr Cash|Bank|AP, and
     * raise a payable when bought on credit.
     */
    private function postNonStockPurchase($request, $product, $refNo, $userData)
    {
        $branch = Branch::where(['id' => $userData->branch_id, 'institution_id' => $userData->institution_id])->first();

        if ($request->payment_method == 'BANK') {
            $cl = CntrlParameter::where(['param_cd' => 'CGL', 'institution_id' => $userData->institution_id])->first();
        } elseif ($request->payment_method == 'CREDIT') {
            $cl = CntrlParameter::where(['param_cd' => 'AP', 'institution_id' => $userData->institution_id])->first();
        } else {
            $cl = CntrlParameter::where(['param_cd' => 'CL', 'institution_id' => $userData->institution_id])->first();
        }
        $gfs = CntrlParameter::where(['param_cd' => 'GFS', 'institution_id' => $userData->institution_id])->first();

        $cgl = str_replace('***', $branch->code, $cl->param_value);
        $cash = GlAccounts::where('acct_no', $cgl)->first();
        $expGl = str_replace('***', $branch->code, $gfs->param_value);
        $exp = GlAccounts::where('acct_no', $expGl)->first();

        $amount = (float) $request->quantity * (float) $request->purchase_price;
        $tranId = $this->generateUuid();
        $date = $this->businessDate($request->date ?? null);
        $desc = "Purchase non-stock item:- $product->name on " . date('Y-m-d H:i:s');

        $this->postTransaction((array) [
            'acct_no' => $expGl, 'acct_type' => $exp->acct_type,
            'contra_acct_no' => $cgl, 'contra_acct_type' => $cash->acct_type,
            'description' => $desc, 'dr_cr_ind' => 'DR/CR', 'tran_amount' => $amount,
            'reversal_flag' => 'N', 'tran_date' => $date, 'tran_cd' => 'EXP',
            'tran_id' => $tranId, 'status' => 'Active', 'institution_id' => $userData->institution_id,
            'branch_id' => $userData->branch_id, 'created_by' => $userData->id, 'created_on' => now(),
            'product_id' => $product->id, 'ext_ref' => $refNo, 'tran' => 'Purchase Non-stock',
        ]);

        $base = [
            'tran_amt' => $amount, 'reversal_flag' => 'N', 'description' => $desc,
            'transaction_date' => $date, 'tran_type' => 'PURCHASE', 'tran_cd' => 'EXP',
            'tran_id' => $tranId, 'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
            'created_by' => $userData->id, 'status' => 'Active',
        ];
        $this->postGlDR((object) array_merge($base, [
            'acct_no' => $expGl, 'acct_type' => $exp->acct_type,
            'contra_acct_no' => $cgl, 'contra_acct_type' => $cash->acct_type,
        ]));
        $this->postGlCR((object) array_merge($base, [
            'acct_no' => $cgl, 'acct_type' => $cash->acct_type,
            'contra_acct_no' => $expGl, 'contra_acct_type' => $exp->acct_type,
        ]));

        if ($request->payment_method == 'CREDIT') {
            $this->createPayable((array) [
                'ref_no' => $refNo, 'tran_id' => $tranId, 'supplier_id' => $request->supplier_id,
                'product_id' => $product->id, 'manufacturer_id' => $request->manufacturer_id,
                'amount' => $amount, 'amount_paid' => 0, 'status' => 'Active',
                'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
                'created_by' => $userData->id, 'created_on' => Carbon::now(),
            ]);
        }
    }

    /**
     * Validate the units-of-measure list submitted with a product.
     * Returns true when valid, otherwise an error message string.
     */
    private function validateProductUnits($units)
    {
        if (!is_array($units) || count($units) === 0) {
            return "At least one unit of measure is required";
        }

        $baseCount = 0;
        $seen = [];
        foreach ($units as $u) {
            if (empty($u['measurement_unit_id'])) {
                return "Each unit must have a measurement unit selected";
            }
            $muId = $u['measurement_unit_id'];
            if (in_array($muId, $seen)) {
                return "A measurement unit can only be added once per product";
            }
            $seen[] = $muId;

            $isBase = !empty($u['is_base']);
            if ($isBase) {
                $baseCount++;
                if ((float) $u['conversion_factor'] != 1.0) {
                    return "The base unit must have a conversion factor of 1";
                }
            } elseif (!isset($u['conversion_factor']) || (float) $u['conversion_factor'] <= 0) {
                return "Conversion factor must be greater than 0";
            }
        }

        if ($baseCount !== 1) {
            return "Exactly one base unit must be selected";
        }

        return true;
    }

    /**
     * Create/update the product_units rows for a product.
     * Existing rows are matched by measurement_unit_id; rows no longer present
     * are soft-archived (never deleted) because past order/invoice items may
     * still reference them. $baseUnitId is the (possibly EFRIS-remapped) base unit.
     */
    private function syncProductUnits($product, array $units, $baseUnitId, $userData, $efrisEnabled = false)
    {
        $now = Carbon::now();
        $existing = ProductUnit::where('product_id', $product->id)->get()->keyBy('measurement_unit_id');
        $incomingUnitIds = [];

        foreach ($units as $u) {
            $isBase = !empty($u['is_base']);
            // base unit is already remapped to a local id ($baseUnitId); non-base
            // units carry EFRIS unit ids when EFRIS is enabled, so map them to a
            // local measurement_unit so their names/conversions resolve everywhere.
            if ($isBase) {
                $muId = $baseUnitId;
            } elseif ($efrisEnabled) {
                $muId = $this->createOrReturnMeasurementUnit($u['measurement_unit_id'])->id;
            } else {
                $muId = $u['measurement_unit_id'];
            }
            $incomingUnitIds[] = $muId;

            $row = $existing->get($muId) ?? new ProductUnit();
            if (!$row->exists) {
                $row->product_id = $product->id;
                $row->institution_id = $userData->institution_id;
                $row->branch_id = $userData->branch_id;
                $row->created_by = $userData->id;
                $row->created_on = $now;
            } else {
                $row->updated_by = $userData->id;
                $row->updated_on = $now;
            }

            $row->measurement_unit_id = $muId;
            $row->conversion_factor = $isBase ? 1 : (float) $u['conversion_factor'];
            $row->purchase_price = (float) $u['purchase_price'];
            $row->selling_price = (float) $u['selling_price'];
            $row->is_base = $isBase;
            $row->status = 'Active';
            $row->save();
        }

        // soft-archive units that were removed in this edit
        foreach ($existing as $muId => $row) {
            if (!in_array($muId, $incomingUnitIds) && $row->status !== 'Archived') {
                $row->status = 'Archived';
                $row->updated_by = $userData->id;
                $row->updated_on = $now;
                $row->save();
            }
        }
    }


    public function restock(Request $request)
    {
        $userData = $this->getUserLogin();
        $institutionData = $this->getUserInstitution();

        // try {
        DB::beginTransaction();

        $product = Product::where(['id' => $request->productId, "institution_id" => $userData->institution_id])->first();
        if (!isset($product)) {
            return $this->genericResponse(false, "Product not found", 400, $product, "restock", $request);
        }

        $stocks =  stock::where(["product_id" => $request->productId, "institution_id" => $userData->institution_id, "branch_id" => $userData->branch_id])->first();

        if (!isset($stocks)) {
            return $this->genericResponse(false, "Stock record not found for this product", 400, null, "restock", $request);
        }

        $stocks->updated_by = $userData->id;
        $stocks->updated_on = Carbon::now();
        // cost is no longer overwritten here — receiveStock() below records a cost
        // layer at this purchase price and refreshes the cached weighted-average cost
        $stocks->selling_price = (float) $request->selling_price;
        $stocks->quantity = (float) $stocks->quantity + $request->quantity;
        $stocks->min_quantity = (float) $request->min_quantity;
        $stocks->max_quantity = (float) $request->max_quantity;
        $stocks->stock_date = $this->businessDate($request->date);
        $stocks->manufactured_date = $request->manufactured_date;
        $stocks->expiry_date = $request->expiry_date;
        $stocks->branch_id = $userData->branch_id;
        $stocks->user_id = $userData->id;
        $stocks->save();

        // add the restocked quantity to the branch's default warehouse sub-ledger
        $this->adjustWarehouseStock($request->productId, $userData->branch_id, (float) $request->quantity);

        $history = new stockHistory();
        $history->created_by = $userData->id;
        $history->created_on = Carbon::now();
        $history->purchase_price = (float) $request->purchase_price;
        $history->selling_price = (float) $request->selling_price;
        $history->discount = $request->discount;
        $history->product_id = $request->productId;
        $history->stock_id = $stocks->id;
        $history->quantity = (float) $request->quantity;
        $history->min_quantity = (float) $request->min_quantity;
        $history->max_quantity = (float) $request->max_quantity;
        $history->institution_id = $userData->institution_id;
        $history->stock_date = $this->businessDate($request->date);
        $history->manufactured_date = $request->manufactured_date;
        $history->expiry_date = $request->expiry_date;
        $history->branch_id = $userData->branch_id;
        $history->user_id = $userData->id;
        $history->status = $request->status;
        $history->source_type = 'RESTOCK'; // a genuine goods receipt
        $history->save();


        $branch = Branch::where(['id' => $userData->branch_id, 'institution_id' => $userData->institution_id])->first();

        // Dr Inventories / Cr cash, bank or payables
        $paymentMethod = $request->payment_method ?? 'CASH';
        $postTran = $this->postStockReceipt($userData, $branch, $product,
            (float) $request->quantity * (float) $request->purchase_price, $paymentMethod,
            $request->date, 'Stocking Product', $product->ref_no, $stocks->id);

        // a credit restock is owed to the supplier like a credit opening purchase
        if ($paymentMethod == "CREDIT" && $postTran) {
            $this->createPayable((array)[
                "ref_no" => $product->ref_no,
                "tran_id" => $postTran->tran_id,
                "supplier_id" => $request->supplier_id ?? $product->supplier_id,
                "product_id" => $product->id,
                "manufacturer_id" => $request->manufacturer_id ?? $product->manufacturer_id,
                "amount" => (float) $postTran->tran_amount,
                "amount_paid" => 0,
                "status" => "Active",
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "created_on" => Carbon::now(),
            ]);
        }


        if (isset($institutionData->is_tax_enabled) && $institutionData->efris_enabled) {
            // updating stock to efris
            $supplier = Supplier::find($product->supplier_id);
            $efrisUnit = $this->getEfrisMeasurementUnitByUnitId($product->measurement_unit_id);

            $this->increaseStock($supplier?->name ?? '', $request->date, $product->ref_no, $request->quantity, $efrisUnit->value,$request->selling_price);
        }

        // record the purchase as a cost layer (valuation engine); carry inventory at
        // standard for STANDARD-cost products via the returned variance
        $variance = $this->receiveStock($request->productId, $userData->branch_id, (float) $request->quantity, (float) $request->purchase_price, 'PURCHASE', $product->ref_no, $this->businessDate($request->date), $userData, $postTran?->tran_id);
        $this->postStandardVariance($userData, $branch, $variance, $postTran?->tran_id, "Purchase price variance for $product->name");

        DB::commit();
        return $this->genericResponse(true, "Product restock successfully", 201, $stocks, "restock", $request);
        // } catch (\Throwable $th) {

        //     throw  new $th;
        // }
    }

    public function getProductList($request)
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();

            $sqlString = "SELECT P.id,P.name, P.product_no, P.category_id, P.sub_category_id, P.manufacturer_id, P.supplier_id, P.measurement_unit_id, P.description,
            P.institution_id, P.user_id, P.status, P.created_by, P.updated_by, P.created_on, P.updated_on, P.created_at, P.ref_no, P.updated_at, C.name AS category_name,
            S.name AS sub_category_name, M.name AS manufacturer, Q.name AS supplier, T.name AS unit, E.purchase_price, E.selling_price, E.discount, E.quantity,
            E.min_quantity, E.max_quantity, B.name AS branch_name, I.name AS institution_name,  P.tax_config, Y.name AS secondary_unit, P.secondary_weight, P.secondary_price, P.secondary_measurement_unit_id,
            P.nature, P.tracks_stock
            FROM products P INNER JOIN product_categories C ON C.id = P.category_id
            LEFT JOIN product_sub_categories S ON P.sub_category_id = S.id
            LEFT JOIN manufacturers M ON  P.manufacturer_id = M.id
            LEFT JOIN suppliers Q ON P.supplier_id = Q.id
            LEFT JOIN measurement_units T ON P.measurement_unit_id = T.id
            INNER JOIN stocks E ON E.product_id = P.id
            INNER JOIN branches B ON B.id = E.branch_id
            INNER JOIN institutions I ON I.id =P.institution_id
            LEFT JOIN measurement_units Y ON Y.id = P.secondary_measurement_unit_id
            WHERE P.status = 'Active'";

            $totalSql = "SELECT COUNT(P.id) AS total
            FROM products P INNER JOIN product_categories C ON C.id = P.category_id
            LEFT JOIN product_sub_categories S ON P.sub_category_id = S.id
            LEFT JOIN manufacturers M ON  P.manufacturer_id = M.id
            LEFT JOIN suppliers Q ON P.supplier_id = Q.id
            LEFT JOIN measurement_units T ON P.measurement_unit_id = T.id
            INNER JOIN stocks E ON E.product_id = P.id
            INNER JOIN branches B ON B.id = E.branch_id
            INNER JOIN institutions I ON I.id =P.institution_id
            LEFT JOIN measurement_units Y ON Y.id = P.secondary_measurement_unit_id
            WHERE P.status = 'Active'";
            if ($isNotAdmin) {
                $totalSql .= " AND E.institution_id = $userData->institution_id AND E.branch_id = $userData->branch_id";
                $sqlString .= " AND E.institution_id = $userData->institution_id AND E.branch_id = $userData->branch_id";
            }
            if (!empty(trim($request->search))) {
                $totalSql .= " AND P.name ILIKE '%" . trim($request->search) . "%'";
                $sqlString .= " AND P.name ILIKE '%" . trim($request->search) . "%'";
            }

            if (!empty(trim($request->startDate))) {
                $startDate = date('Y-m-d', strtotime(trim($request->startDate))); // only date part
                $sqlString .= " AND P.created_at::date >= '$startDate'";
                $totalSql .= " AND P.created_at::date >= '$startDate'";
            }
            if (!empty(trim($request->endDate))) {
                $endDate = date('Y-m-d', strtotime(trim($request->endDate))); // only date part
                $sqlString .= " AND P.created_at::date <= '$endDate'";
                $totalSql .= " AND P.created_at::date <= '$endDate'";
            }

            if (isset($request->id)) {
                $sqlString .= " AND P.id = $request->id";
                $totalSql .= " AND P.id = $request->id";
            }

            $sqlString .= " ORDER BY P.id DESC ";

            $products = $this->paginate($request->rowPerPage, $totalSql, $sqlString);

            // attach the configured units of measure to every product
            $ids = collect($products->items())->pluck('id')->all();
            $unitsByProduct = $this->getUnitsForProducts($ids);
            $products->getCollection()->transform(function ($row) use ($unitsByProduct) {
                $row->units = $unitsByProduct->get($row->id, collect())->values();
                $this->useBranchBaseUnitPrice($row);
                return $row;
            });

            return $products;
        } catch (\Throwable $th) {
            // throw new $th;
            throw $th;
        }
    }

    /**
     * Load Active product_units for the given product ids, shaped for the
     * frontend (CreateProduct edit-load + POS), grouped by product_id.
     */
    private function getUnitsForProducts($productIds)
    {
        if (empty($productIds)) {
            return collect();
        }

        return ProductUnit::with('measurement')
            ->whereIn('product_id', $productIds)
            ->where('status', 'Active')
            ->orderByDesc('is_base')
            ->get()
            ->map(function ($u) {
                return (object)[
                    'id'                  => $u->id,
                    'product_id'          => $u->product_id,
                    'measurement_unit_id' => $u->measurement_unit_id,
                    'name'                => $u->measurement->name ?? null,
                    'conversion_factor'   => (float) $u->conversion_factor,
                    'purchase_price'      => (float) $u->purchase_price,
                    'selling_price'       => (float) $u->selling_price,
                    'is_base'             => (bool) $u->is_base,
                ];
            })
            ->groupBy('product_id');
    }

    /**
     * Each branch sells at its own stocks.selling_price, but product_units rows
     * are shared by every branch. The web and mobile POS take the base unit's
     * selling_price, so give the base unit this row's branch price. Units are
     * cloned because rows of the same product (one per branch) share them.
     */
    private function useBranchBaseUnitPrice($row)
    {
        $row->units = collect($row->units ?? [])->map(function ($u) use ($row) {
            $u = clone $u;
            if ($u->is_base) {
                $u->selling_price = (float) $row->selling_price;
            }
            return $u;
        })->values();
    }


    public function getProducts(Request $request)
    {
        // $userData = $this->getUserLogin();
        // // $userData = auth()->user();
        // $isNotAdmin = $this->isNotAdmin();

        // $sqlString = "SELECT P.id,P.name, P.product_no, P.category_id, P.sub_category_id, P.manufacturer_id, P.supplier_id, P.measurement_unit_id, P.description,
        //     P.institution_id, P.user_id, P.status, P.created_by, P.updated_by, P.created_on, P.updated_on, P.created_at, P.ref_no, P.updated_at, C.name AS category_name,
        //     S.name AS sub_category_name, M.name AS manufacturer, Q.name AS supplier, T.name AS unit, E.purchase_price, E.selling_price, E.discount, E.quantity,
        //     E.min_quantity, E.max_quantity, B.name AS branch_name, I.name AS institution_name,  P.tax_config, Y.name AS secondary_unit, P.secondary_weight, P.secondary_price, P.secondary_measurement_unit_id
        //     FROM products P INNER JOIN product_categories C ON C.id = P.category_id
        //     LEFT JOIN product_sub_categories S ON P.sub_category_id = S.id
        //     LEFT JOIN manufacturers M ON  P.manufacturer_id = M.id
        //     LEFT JOIN suppliers Q ON P.supplier_id = Q.id
        //     LEFT JOIN measurement_units T ON P.measurement_unit_id = T.id
        //     INNER JOIN stocks E ON E.product_id = P.id
        //     INNER JOIN branches B ON B.id = E.branch_id
        //     INNER JOIN institutions I ON I.id =P.institution_id
        //     LEFT JOIN measurement_units Y ON Y.id = P.secondary_measurement_unit_id
        //     WHERE P.status = 'Active'";

        // $totalSql = "SELECT COUNT(P.id) AS total
        //     FROM products P INNER JOIN product_categories C ON C.id = P.category_id
        //     LEFT JOIN product_sub_categories S ON P.sub_category_id = S.id
        //     LEFT JOIN manufacturers M ON  P.manufacturer_id = M.id
        //     LEFT JOIN suppliers Q ON P.supplier_id = Q.id
        //     LEFT JOIN measurement_units T ON P.measurement_unit_id = T.id
        //     INNER JOIN stocks E ON E.product_id = P.id
        //     INNER JOIN branches B ON B.id = E.branch_id
        //     INNER JOIN institutions I ON I.id =P.institution_id
        //     LEFT JOIN measurement_units Y ON Y.id = P.secondary_measurement_unit_id
        //     WHERE P.status = 'Active'";
        // if ($isNotAdmin) {
        //     $totalSql .= " AND E.institution_id = $userData->institution_id AND E.branch_id = $userData->branch_id";
        //     $sqlString .= " AND E.institution_id = $userData->institution_id AND E.branch_id = $userData->branch_id";
        // }
        // if (!empty(trim($request->search))) {
        //     $totalSql .= " AND P.name ILIKE '%" . trim($request->search) . "%'";
        //     $sqlString .= " AND P.name ILIKE '%" . trim($request->search) . "%'";
        // }

        // if (!empty(trim($request->startDate))) {
        //     $startDate = date('Y-m-d', strtotime(trim($request->startDate))); // only date part
        //     $sqlString .= " AND P.created_at::date >= '$startDate'";
        //     $totalSql .= " AND P.created_at::date >= '$startDate'";
        // }
        // if (!empty(trim($request->endDate))) {
        //     $endDate = date('Y-m-d', strtotime(trim($request->endDate))); // only date part
        //     $sqlString .= " AND P.created_at::date <= '$endDate'";
        //     $totalSql .= " AND P.created_at::date <= '$endDate'";
        // }

        // if (isset($request->id)){
        //     $sqlString .= " AND P.id = $request->id";
        //     $totalSql .= " AND P.id = $request->id";
        // }

        // $sqlString .= " ORDER BY P.id DESC ";

        // $products = $this->paginate($request->rowPerPage, $totalSql, $sqlString);
        $products = $this->getProductList($request);
        return $this->genericResponse(true, "Product list", 200, $products, "getProducts", []);
    }

    public function getProductDetails(Request $request)
    {
        $userData = $this->getUserLogin();
        $isNotAdmin = $this->isNotAdmin();

        // $sqlString="SELECT P.id,P.name, P.product_no, P.category_id, P.sub_category_id, P.manufacturer_id, P.supplier_id, P.measurement_unit_id, P.description,
        // P.institution_id, P.user_id, P.status, P.created_by, P.type_id, P.gauge_id, E.stock_date, P.updated_by, P.created_on, P.updated_on, P.created_at, P.updated_at, C.name AS category_name,
        // S.name AS sub_category_name, M.name AS manufacturer, Q.name AS supplier, T.name AS unit, E.purchase_price, E.selling_price, E.discount, E.quantity,
        // E.min_quantity, E.id AS stock_id, E.max_quantity, B.name AS branch_name, I.name AS institution_name, P.tax_config
        // FROM products P INNER JOIN product_categories C ON C.id = P.category_id
        // LEFT JOIN product_sub_categories S ON P.sub_category_id = S.id
        // LEFT JOIN manufacturers M ON  P.manufacturer_id = M.id
        // LEFT JOIN suppliers Q ON P.supplier_id = Q.id
        // LEFT JOIN measurement_units T ON P.measurement_unit_id = T.id
        // INNER JOIN stocks E ON E.product_id = P.id
        // INNER JOIN branches B ON B.id = E.branch_id
        // INNER JOIN institutions I ON I.id =P.institution_id ";
        $sqlString = "SELECT P.id, P.name, P.product_no, P.category_id, P.sub_category_id, P.manufacturer_id, P.supplier_id, P.measurement_unit_id,
        P.description, P.institution_id, P.user_id, P.status, P.created_by, P.updated_by, P.created_on, P.updated_on, P.created_at,
        P.updated_at, P.type_id, P.gauge_id, P.ref_no, P.tax_config, S.purchase_price, S.selling_price, S.discount, S.quantity,
        S.min_quantity, S.max_quantity, S.stock_date, S.manufactured_date, S.expiry_date, C.name AS category_name, T.name AS type_name,
        K.name AS sub_category_name, G.name AS gauge_name, M.name AS manufacturer_name, M.website AS manufacturer_website,
        M.email AS manufacturer_email, M.phone_number AS manufacturer_phone_number,MC.name AS manufacturer_country, Q.name AS supplier_name,
        Q.website AS supplier_website, Q.email AS supplier_email, Q.phone_number AS supplier_phone_number, QC.name AS supplier_country,
        MU.name AS measurement_unit, CONCAT(U.first_name,' ',U.last_name, ' ', U.other_name) AS user_name, I.name AS institution_name,
        B.name AS branch_name
        FROM products P INNER JOIN stocks S ON S.product_id = P.id
        INNER JOIN product_categories C ON C.id = P.category_id
        INNER JOIN product_types T ON T.id = P.type_id
        LEFT JOIN product_sub_categories K ON K.id = P.sub_category_id
        LEFT JOIN product_gauges G ON G.id = P.gauge_id
        LEFT JOIN manufacturers M ON M.id = P.manufacturer_id
        LEFT JOIN country_refs MC ON M.country_id = MC.id
        LEFT JOIN suppliers Q ON Q.id = P.supplier_id
        LEFT JOIN country_refs QC ON Q.country_id = QC.id
        LEFT JOIN measurement_units MU ON MU.id = P.measurement_unit_id
        INNER JOIN users U ON U.id = P.user_id
        INNER JOIN institutions I ON I.id = P.institution_id
        LEFT JOIN branches B ON B.id = S.branch_id ";
        $sqlString .= " WHERE P.id = $request->id ";
        if ($isNotAdmin) {
            $sqlString .= " AND P.institution_id = $userData->institution_id AND S.branch_id = $userData->branch_id ";
        }
        $sqlString .= " ORDER BY P.id DESC";
        $product = DB::select($sqlString);

        // attach the configured units of measure
        if (!empty($product)) {
            $unitsByProduct = $this->getUnitsForProducts([$product[0]->id]);
            $product[0]->units = $unitsByProduct->get($product[0]->id, collect())->values();
        }

        return $this->genericResponse(true, "Product list", 200, $product, "getProductDetails", $request);
    }


    public function approveBulkProducts(Request  $request)
    {
        foreach ($request->products as $product) {
            $newProduct = TempProduct::find($product["id"]);
            $newProduct->user_id = $newProduct->created_by;
            $newProduct->status = "Active";
            //            $response = $this->newMember($newProduct);
            $newRequest = (object) [
                'name'   => $newProduct->name,
                'product_no'   => $newProduct->product_no,
                'category_id'   => $newProduct->category_id,
                'sub_category_id'   => $newProduct->sub_category_id,
                'manufacturer_id'   => $newProduct->manufacturer_id,
                'supplier_id'   => $newProduct->supplier_id,
                'measurement_unit_id'   => $newProduct->measurement_unit_id,
                'description'   => $newProduct->description,
                'type_id'   => $newProduct->type_id,
                'gauge_id'   => $newProduct->gauge_id,
                'tax_config'   => $newProduct->tax_config,
                'quantity'   => $newProduct->quantity,
                'purchase_price'   => $newProduct->purchase_price,
                'date'   => $newProduct->stock_date,
                'discount'   => $newProduct->discount,
                'selling_price'   => $newProduct->selling_price,
                'min_quantity'   => $newProduct->min_quantity,
                'max_quantity'   => $newProduct->max_quantity,
                'manufactured_date'   => $newProduct->manufactured_date,
                'expiry_date'   => $newProduct->expiry_date,
                'status'   => 'Active',
                'id'   => null,
                'secondary_measurement_unit_id' => null,
                'secondary_weight' => null,
                'secondary_price' => null,
                // uploaded stock is opening stock, not a cash purchase
                'payment_method' => 'OPENING',
                // single base unit so bulk-approved products get a product_units row
                'units' => [[
                    'measurement_unit_id' => $newProduct->measurement_unit_id,
                    'conversion_factor'   => 1,
                    'purchase_price'      => $newProduct->purchase_price,
                    'selling_price'       => $newProduct->selling_price,
                    'is_base'             => true,
                ]],
            ];

            $response = $this->createNewProduct($newRequest);
            if ($response->product) {
                //                unset($newProduct->user_id);?
                $newProduct->save();
            }
        }
        return $this->genericResponse(true, "Approved Successfully", 200, [], "approveBulkProducts", $request);
    }


    public function getGlSum($acctNo)
    {
        $glAcct = GlAccounts::where('acct_no', $acctNo)->first();
        $totalSaleDR = (float) DB::table('gl_histories')
            ->where('acct_no', $acctNo)
            ->where('dr_cr_ind', 'Dr')
            // ->whereBetween('transaction_date', [$request->fromDate, $request->toDate])
            ->sum('tran_amount');

        $totalSaleCR = (float) DB::table('gl_histories')
            ->where('acct_no', $acctNo)
            ->where('dr_cr_ind', 'Cr')
            // ->whereBetween('transaction_date', [$request->fromDate, $request->toDate])
            ->sum('tran_amount');

        if ($glAcct->acct_type == 'ASSET' || $glAcct->acct_type == 'EXPENSE') {
        }
    }

    public function archiveProduct(Request $request)
    {
        try {
            $userData = $this->getUserLogin();
            $message = '';

            $product = Product::where(['id' => $request->productId, 'institution_id' => $userData->institution_id])->first();
            if (!isset($product)) {
                return $this->genericResponse(false, 'Product not found', 404, [], "archiveProduct", $request);
            }

            if ($request->status == 'Archived') {
                $message = 'product archived successfully';
            } else if ($request->status == 'Active') {
                $message = 'product activated successfully';
            }

            // Archiving only hides the product. Its stock, cost layers and postings
            // stay as they are, so inventory on the balance sheet still matches stock
            // on hand; write stock off with a stock adjustment if it is really gone.
            // (This used to require, and re-status, the opening purchase transaction,
            // so products without one could not be archived.)
            $product->status = $request->status;
            $product->updated_by = $userData->id;
            $product->updated_on = Carbon::now();
            $product->save();
            return $this->genericResponse(true, $message, 201, $product, "archiveProduct", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "archiveProduct", $request);
        }
    }


    public function getInvoiceDetails(Request $request)
    {
        try {
            $invoice = Invoice::find($request->id);

            if (!isset($invoice)) {
                return $this->genericResponse(false, "Invoice not found", 400, $invoice, "getInvoiceDetails", $request);
            }

            $invoiceItems = InvoiceItem::where(['invoice_id' => $invoice->id])->get();

            $response =  array();
            foreach ($invoiceItems as $item) {
                $postData = (object)["id" => $item['product_id']];

                $request->merge(["id" => $item['product_id']]);

                $results = $this->getProductList($request);

                $response = $results;

                array_merge($response, $results['data']['data']);
            }

            return $this->genericResponse(true, "Invoice details", 200, $response, "getInvoiceDetails", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, "Unauthenticated", 401, null, "getInvoiceDetails", $request);
        }
    }


    public function testEfrisProductCreation(Request $request)
    {
        $institutionData = $this->getUserInstitution();
        $payload = [
            'products' => [
                [
                    'goodsName' => 'apple seedsd',
                    'goodsCode' => 'Z0123',
                    'measureUnit' => '101',
                    'unitPrice' => '6999.99',
                    'currency' => '101',
                    'commodityCategoryId' => '95141708',
                    'haveExciseTax' => '102',
                    'description' => '1',
                    'stockPrewarning' => '10',
                    'pieceMeasureUnit' => '',
                    'havePieceUnit' => '102',
                    'pieceUnitPrice' => '',
                    'packageScaledValue' => '',
                    'pieceScaledValue' => '',
                    'exciseDutyCode' => '',
                ],
            ],
        ];

        $response = $this->efrisService->request('GET', $institutionData->tin . '/master-data', [], []);
        // $response = $this->efrisService->request('POST', $institutionData->tin.'/products/create', [], $payload);

        log::info("response");

        // return $response;
        return $this->genericResponse(true, "Efris product creation", 200, $response->json(), "testEfrisProductCreation", $request);
    }


    public function efrisCreateProduct($request, $refNo, $unit, $category, $excisable)
    {
        try {
            $institutionData = $this->getUserInstitution();
            $payload = [
                'products' => [
                    [
                        'goodsName' => $request->name,
                        'goodsCode' => $refNo,
                        'measureUnit' => $unit,
                        'unitPrice' => $request->selling_price,
                        'currency' => '101',
                        'commodityCategoryId' => $category,
                        'haveExciseTax' => $excisable,
                        'description' => '1',
                        'stockPrewarning' => '10',
                        'pieceMeasureUnit' => '',
                        'havePieceUnit' => '102',
                        'pieceUnitPrice' => '',
                        'packageScaledValue' => '',
                        'pieceScaledValue' => '',
                        'exciseDutyCode' => '',
                    ],
                ],
            ];


            // $response = $efrisApiService->request('GET', '1033614630/auth/server-time', [],[]);
            $response = $this->efrisService->request('POST', $institutionData->tin . '/products/create', [], $payload);

            log::info("**************** EFRIS RESPONSE => Creating product ****************");
            log::info($response);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function efrisCreateOpeningStock($supplier, $date, $refNo, $qty, $price, $unit)
    {
        try {
            $institutionData = $this->getUserInstitution();
            $payload = [
                "supplierName" => $supplier,
                "supplierTin" => "",
                "stockInDate" => Carbon::parse($date)->format('Y-m-d'),
                "remarks" => "Opening stock entry",
                "items" => [
                    [
                        "goodsCode" => $refNo,
                        "measureUnit" => $unit,
                        "quantity" => $qty,
                        "unitPrice" => $price
                    ]
                ]
            ];
            $response = $this->efrisService->request('POST', $institutionData->tin . '/products/add-opening-stock', [], $payload);

            log::info("**************** EFRIS RESPONSE => Creating opening stock ****************");
            log::info($response);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function increaseStock($supplier, $date, $refNo, $qty, $unit, $price)
    {
        try {
            $institutionData = $this->getUserInstitution();
            $payload = [
                "supplierName" => $supplier,
                "stockInType" => "102",
                "supplierTin" => "",
                "stockInDate" => Carbon::parse($date)->format('Y-m-d'),
                "remarks" => "purchase",
                "items" => [
                    [
                        "goodsCode" => $refNo,
                        "measureUnit" => $unit,
                        "quantity" => $qty,
                        "unitPrice" => $price
                    ]
                ]
            ];

            $response = $this->efrisService->request('POST', $institutionData->tin . '/products/increase-stock', [], $payload);

            log::info("**************** EFRIS RESPONSE => Increasing stock ****************");
            log::info($response);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function decreaseStock($refNo, $qty, $price)
    {
        try {

            $institutionData = $this->getUserInstitution();
            $payload = [
                "adjustType" => "102",
                "remarks" => "Stock adjustment",
                "items" => [
                    [
                        "goodsCode" => $refNo,
                        "quantity" => $qty,
                        "unitPrice" => $price
                    ]
                ]
            ];

            $response = $this->efrisService->request('POST', $institutionData->tin . '/products/decrease-stock', [], $payload);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function getEfrisMeasurementUnits()
    {
        try {
            $institutionData = $this->getUserInstitution();
            $response = $this->efrisService->request('GET', $institutionData->tin . '/measure-units', [], []);

            $results = $response->json();

            $efrisResponse = [];
            foreach ($results['data']['rateUnit'] as $key => $unit) {
                $efrisResults =  $this->createEfrisMeasurementUnit($unit);
                $efrisResponse[] = $efrisResults;
            }
            return $this->genericResponse(true, "Efris measurement units", 200, $efrisResponse, "getEfrisMeasurementUnits", []);
        } catch (\Throwable $th) {
            // throw $th;
            return $this->genericResponse(false, "Unauthenticated", 401, $th->getMessage(), "getEfrisMeasurementUnits", []);
        }
    }




    public function createEfrisMeasurementUnit(array $unit): EfrisMeasurementUnits
    {
        return DB::transaction(function () use ($unit) {

            $user = $this->getUserLogin();

            return EfrisMeasurementUnits::firstOrCreate(
                ['value' => $unit['value']], // unique constraint
                [
                    'name'        => $unit['name'],
                    'value'       => $unit['value'],
                    'description' => Arr::get($unit, 'description'),
                    'user_id'     => $user->id,
                    'created_by'  => $user->id,
                    'created_at'  => Carbon::now(),
                    'status'      => 'Active',

                ]
            );
        });
    }


    public function createOrReturnMeasurementUnit(int $efrisUnitId): MeasurementUnit
    {
        $user = $this->getUserLogin();

        $efrisUnit = EfrisMeasurementUnits::findOrFail($efrisUnitId);

        return MeasurementUnit::firstOrCreate(
            [
                // BUSINESS KEY (not id)
                'efris_code'     => $efrisUnit->value,
                'institution_id' => $user->institution_id,
            ],
            [
                'name'         => $efrisUnit->name,
                'description'  => $efrisUnit->description,
                'branch_id'    => $user->branch_id,
                'status'       => 'Active',
                'efris_synced' => true,
                'created_by'   => $user->id,
                'user_id'      => $user->id,
            ]
        );
    }




    public function createOrReturnProductCategories(int $efrisCategoryId): ProductCategory
    {
        $user = $this->getUserLogin();

        $efrisCategory = EfrisComodityCategory::findOrFail($efrisCategoryId);

        return ProductCategory::firstOrCreate(
            [
                // UNIQUE BUSINESS KEY
                'efris_code'     => $efrisCategory->commodity_category_code,
                'institution_id' => $user->institution_id,
            ],
            [
                'name'         => $efrisCategory->commodity_category_name,
                'description'  => $efrisCategory->commodity_category_name,
                'branch_id'    => $user->branch_id,
                'status'       => 'Active',
                'efris_synced' => true,
                'created_by'   => $user->id,
                'user_id'      => $user->id,
            ]
        );
    }



    public function getEfrisMeasurementUnitByUnitId($unitId)
    {
        $unit = measurementUnit::find($unitId);
        $efrisUnit = EfrisMeasurementUnits::where(['value' => $unit->efris_code])->first();
        return $efrisUnit;
    }


    public function getEfrisCommodityCatByProdCatId($prodCatId)
    {
        $cat = ProductCategory::find($prodCatId);
        $efrisCat = EfrisComodityCategory::where(['commodity_category_code' => $cat->efris_code])->first();
        return $efrisCat;
    }
}
