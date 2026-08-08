<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\PaymentTerm;
use App\Models\SupplierContact;
use App\Models\SupplierRating;
use App\Models\SupplierContract;
use App\Models\SupplierDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierController extends Controller
{
    // ----------------------------------------------------------- Supplier ----

    public function createSupplier(Request $request)
    {
        $userData = $this->getUserLogin();
        $supplier = new Supplier();
        $supplier->name = $request->name;
        $supplier->website = $request->website;
        $supplier->email = $request->email;
        $supplier->phone_number = $request->phone_number;
        $supplier->country_id = $request->country_id;
        $supplier->address = $request->address;
        $supplier->city_id = $request->city_id;
        $supplier->payment_term_id = $request->payment_term_id ?: null;
        $supplier->credit_limit = (float) ($request->credit_limit ?? 0);
        $supplier->tin = $request->tin;
        $supplier->institution_id = $userData->institution_id;
        $supplier->branch_id = $userData->branch_id;
        $supplier->user_id = $userData->id;
        $supplier->description = $request->description;
        $supplier->status = $request->status ?: 'Active';
        $supplier->created_by = $userData->id;
        $supplier->created_on = now();
        $supplier->save();

        return $this->genericResponse(true, "Supplier created successfully", 201, $supplier, "createSupplier", $request);
    }

    public function updateSupplier(Request $request)
    {
        $userData = $this->getUserLogin();
        $supplier = Supplier::where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!$supplier) {
            return $this->genericResponse(false, "Supplier not found", 400, null, "updateSupplier", $request);
        }
        foreach (['name', 'website', 'email', 'phone_number', 'country_id', 'address', 'city_id', 'tin', 'description'] as $f) {
            if ($request->has($f)) {
                $supplier->$f = $request->$f;
            }
        }
        $supplier->payment_term_id = $request->payment_term_id ?: $supplier->payment_term_id;
        if ($request->has('credit_limit')) {
            $supplier->credit_limit = (float) $request->credit_limit;
        }
        if ($request->status) {
            $supplier->status = $request->status;
        }
        $supplier->updated_by = $userData->id;
        $supplier->updated_on = now();
        $supplier->save();

        return $this->genericResponse(true, "Supplier updated successfully", 200, $supplier, "updateSupplier", $request);
    }

    /**
     * Supplier list with payment term + outstanding payable for the table.
     */
    public function getSuppliers()
    {
        $userData = $this->getUserLogin();
        $rows = DB::table('suppliers as s')
            ->leftJoin('payment_terms as pt', 'pt.id', '=', 's.payment_term_id')
            ->where(['s.status' => 'Active', 's.institution_id' => $userData->institution_id])
            ->select('s.*', 'pt.name as payment_term')
            ->orderBy('s.name', 'asc')->get();
        $rows->transform(function ($s) use ($userData) {
            $s->outstanding = (float) DB::table('payables')
                ->where(['supplier_id' => $s->id, 'status' => 'Active', 'institution_id' => $userData->institution_id])
                ->sum(DB::raw('amount - amount_paid'));
            return $s;
        });
        return $this->genericResponse(true, "Suppliers fetched successfully", 200, $rows, "getSuppliers", []);
    }

    /**
     * Full supplier profile: info + term + contacts + rating averages + counts +
     * contracts + documents + purchase/payable summary.
     */
    public function getSupplierProfile(Request $request)
    {
        $userData = $this->getUserLogin();
        $inst = (int) $userData->institution_id;
        $supplier = Supplier::with(['paymentTerm', 'contacts', 'contracts', 'documents'])
            ->where(['id' => $request->id, 'institution_id' => $inst])->first();
        if (!$supplier) {
            return $this->genericResponse(false, "Supplier not found", 400, null, "getSupplierProfile", $request);
        }
        $sid = (int) $supplier->id;

        $avg = DB::table('supplier_ratings')->where('supplier_id', $sid)
            ->selectRaw('COUNT(*) c, COALESCE(AVG(quality),0) quality, COALESCE(AVG(delivery),0) delivery,
                COALESCE(AVG(price),0) price, COALESCE(AVG(communication),0) communication, COALESCE(AVG(overall),0) overall')->first();

        $productCount = (int) DB::table('products')->where(['supplier_id' => $sid, 'institution_id' => $inst])->count();
        $totalPurchased = (float) DB::table('stock_histories as h')
            ->join('products as p', 'p.id', '=', 'h.product_id')
            ->where('p.supplier_id', $sid)->where('h.institution_id', $inst)
            ->sum(DB::raw('h.quantity * h.purchase_price'));
        $outstanding = (float) DB::table('payables')
            ->where(['supplier_id' => $sid, 'status' => 'Active', 'institution_id' => $inst])
            ->sum(DB::raw('amount - amount_paid'));

        return $this->genericResponse(true, "Supplier profile fetched successfully", 200, [
            'supplier' => $supplier,
            'ratings_summary' => $avg,
            'summary' => [
                'products' => $productCount,
                'total_purchased' => $totalPurchased,
                'outstanding' => $outstanding,
            ],
        ], "getSupplierProfile", $request);
    }

    public function getSupplierProducts(Request $request)
    {
        $userData = $this->getUserLogin();
        $rows = DB::table('products as p')
            ->leftJoin('stocks as s', 's.product_id', '=', 'p.id')
            ->leftJoin('product_categories as c', 'c.id', '=', 'p.category_id')
            ->where(['p.supplier_id' => $request->id, 'p.institution_id' => $userData->institution_id])
            ->select('p.id', 'p.name', 'p.ref_no', 'c.name as category', 's.quantity', 's.purchase_price', 's.selling_price')
            ->orderBy('p.name')->get();
        return $this->genericResponse(true, "Supplier products fetched successfully", 200, $rows, "getSupplierProducts", $request);
    }

    /**
     * Delivery history (read-only): goods received (stock_histories for this
     * supplier's products) + payables owed to the supplier.
     */
    public function getSupplierDeliveries(Request $request)
    {
        $userData = $this->getUserLogin();
        $inst = (int) $userData->institution_id;
        $deliveries = DB::table('stock_histories as h')
            ->join('products as p', 'p.id', '=', 'h.product_id')
            ->where('p.supplier_id', $request->id)->where('h.institution_id', $inst)
            ->select('h.id', 'h.stock_date', 'p.name as product', 'h.quantity', 'h.purchase_price',
                DB::raw('(h.quantity * h.purchase_price) as cost'))
            ->orderByDesc('h.stock_date')->limit(200)->get();
        $payables = DB::table('payables')
            ->where(['supplier_id' => $request->id, 'institution_id' => $inst])
            ->select('id', 'ref_no', 'amount', 'amount_paid', 'status', 'created_on',
                DB::raw('(amount - amount_paid) as outstanding'))
            ->orderByDesc('id')->limit(200)->get();
        return $this->genericResponse(true, "Deliveries fetched successfully", 200, [
            'deliveries' => $deliveries, 'payables' => $payables,
        ], "getSupplierDeliveries", $request);
    }

    // ------------------------------------------------------------ Contacts ---

    public function createSupplierContact(Request $request)
    {
        $userData = $this->getUserLogin();
        if (empty($request->supplier_id) || empty($request->name)) {
            return $this->genericResponse(false, "Supplier and contact name are required", 400, null, "createSupplierContact", $request);
        }
        if ($request->is_primary) {
            SupplierContact::where('supplier_id', $request->supplier_id)->update(['is_primary' => false]);
        }
        $contact = SupplierContact::create([
            'supplier_id' => $request->supplier_id, 'name' => $request->name, 'title' => $request->title,
            'email' => $request->email, 'phone' => $request->phone, 'is_primary' => (bool) $request->is_primary,
            'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
            'status' => 'Active', 'created_by' => $userData->id, 'created_on' => now(),
        ]);
        return $this->genericResponse(true, "Contact added successfully", 201, $contact, "createSupplierContact", $request);
    }

    public function getSupplierContacts(Request $request)
    {
        $contacts = SupplierContact::where(['supplier_id' => $request->supplier_id, 'status' => 'Active'])->orderByDesc('is_primary')->get();
        return $this->genericResponse(true, "Contacts fetched successfully", 200, $contacts, "getSupplierContacts", $request);
    }

    public function deleteSupplierContact(Request $request)
    {
        SupplierContact::where('id', $request->id)->delete();
        return $this->genericResponse(true, "Contact removed successfully", 200, null, "deleteSupplierContact", $request);
    }

    // ------------------------------------------------------------- Ratings ---

    public function addSupplierRating(Request $request)
    {
        $userData = $this->getUserLogin();
        $q = (float) $request->quality;
        $d = (float) $request->delivery;
        $p = (float) $request->price;
        $c = (float) $request->communication;
        if (empty($request->supplier_id) || $q <= 0 || $d <= 0 || $p <= 0 || $c <= 0) {
            return $this->genericResponse(false, "Rate all four criteria (1-5)", 400, null, "addSupplierRating", $request);
        }
        $overall = round(($q + $d + $p + $c) / 4, 2);
        $rating = SupplierRating::create([
            'supplier_id' => $request->supplier_id, 'quality' => $q, 'delivery' => $d, 'price' => $p, 'communication' => $c,
            'overall' => $overall, 'comment' => $request->comment, 'rated_by' => $userData->id, 'rating_date' => now(),
            'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id, 'status' => 'Active', 'created_on' => now(),
        ]);
        // refresh the cached overall rating on the supplier
        $avg = (float) SupplierRating::where('supplier_id', $request->supplier_id)->avg('overall');
        Supplier::where('id', $request->supplier_id)->update(['rating' => round($avg, 2)]);

        return $this->genericResponse(true, "Rating added successfully", 201, $rating, "addSupplierRating", $request);
    }

    public function getSupplierRatings(Request $request)
    {
        $ratings = DB::table('supplier_ratings as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.rated_by')
            ->where('r.supplier_id', $request->supplier_id)
            ->select('r.*', DB::raw("CONCAT(u.first_name,' ',u.last_name) as rated_by_name"))
            ->orderByDesc('r.id')->get();
        return $this->genericResponse(true, "Ratings fetched successfully", 200, $ratings, "getSupplierRatings", $request);
    }

    // ----------------------------------------------------------- Contracts ---

    public function createSupplierContract(Request $request)
    {
        $userData = $this->getUserLogin();
        if (empty($request->supplier_id) || empty($request->title)) {
            return $this->genericResponse(false, "Supplier and contract title are required", 400, null, "createSupplierContract", $request);
        }
        $filePath = null;
        if (!empty($request->file)) {
            try {
                $filePath = $this->saveBase64File($request->file, 'suppliers/contracts');
            } catch (\Throwable $th) {
                return $this->genericResponse(false, $th->getMessage(), 400, null, "createSupplierContract", $request);
            }
        }
        $contract = SupplierContract::create([
            'supplier_id' => $request->supplier_id, 'title' => $request->title, 'contract_no' => $request->contract_no,
            'start_date' => $request->start_date ?: null, 'end_date' => $request->end_date ?: null,
            'value' => (float) ($request->value ?? 0), 'status' => $request->status ?: 'Active',
            'file_path' => $filePath, 'notes' => $request->notes,
            'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
            'created_by' => $userData->id, 'created_on' => now(),
        ]);
        return $this->genericResponse(true, "Contract saved successfully", 201, $contract, "createSupplierContract", $request);
    }

    public function getSupplierContracts(Request $request)
    {
        $contracts = SupplierContract::where('supplier_id', $request->supplier_id)->orderByDesc('id')->get();
        return $this->genericResponse(true, "Contracts fetched successfully", 200, $contracts, "getSupplierContracts", $request);
    }

    public function deleteSupplierContract(Request $request)
    {
        SupplierContract::where('id', $request->id)->delete();
        return $this->genericResponse(true, "Contract removed successfully", 200, null, "deleteSupplierContract", $request);
    }

    // ----------------------------------------------------------- Documents ---

    public function createSupplierDocument(Request $request)
    {
        $userData = $this->getUserLogin();
        if (empty($request->supplier_id) || empty($request->name)) {
            return $this->genericResponse(false, "Supplier and document name are required", 400, null, "createSupplierDocument", $request);
        }
        $filePath = null;
        if (!empty($request->file)) {
            try {
                $filePath = $this->saveBase64File($request->file, 'suppliers/documents');
            } catch (\Throwable $th) {
                return $this->genericResponse(false, $th->getMessage(), 400, null, "createSupplierDocument", $request);
            }
        }
        $doc = SupplierDocument::create([
            'supplier_id' => $request->supplier_id, 'name' => $request->name, 'type' => $request->type,
            'file_path' => $filePath, 'expiry_date' => $request->expiry_date ?: null, 'status' => 'Active',
            'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
            'created_by' => $userData->id, 'created_on' => now(),
        ]);
        return $this->genericResponse(true, "Document saved successfully", 201, $doc, "createSupplierDocument", $request);
    }

    public function getSupplierDocuments(Request $request)
    {
        $docs = SupplierDocument::where('supplier_id', $request->supplier_id)->orderByDesc('id')->get();
        return $this->genericResponse(true, "Documents fetched successfully", 200, $docs, "getSupplierDocuments", $request);
    }

    public function deleteSupplierDocument(Request $request)
    {
        SupplierDocument::where('id', $request->id)->delete();
        return $this->genericResponse(true, "Document removed successfully", 200, null, "deleteSupplierDocument", $request);
    }

    // ------------------------------------------------------- Payment terms ---

    public function createPaymentTerm(Request $request)
    {
        $userData = $this->getUserLogin();
        if (empty($request->name)) {
            return $this->genericResponse(false, "Payment term name is required", 400, null, "createPaymentTerm", $request);
        }
        $term = PaymentTerm::create([
            'name' => $request->name, 'days' => (int) ($request->days ?? 0), 'description' => $request->description,
            'institution_id' => $userData->institution_id, 'status' => 'Active',
            'created_by' => $userData->id, 'created_on' => now(),
        ]);
        return $this->genericResponse(true, "Payment term created successfully", 201, $term, "createPaymentTerm", $request);
    }

    public function getPaymentTerms()
    {
        $userData = $this->getUserLogin();
        $terms = PaymentTerm::where(['status' => 'Active', 'institution_id' => $userData->institution_id])->orderBy('days')->get();
        return $this->genericResponse(true, "Payment terms fetched successfully", 200, $terms, "getPaymentTerms", []);
    }
}
