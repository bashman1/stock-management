<?php

namespace App\Http\Controllers;

use App\Models\Manufacturer;
use App\Models\ManufacturerContact;
use App\Models\ManufacturerRating;
use App\Models\ManufacturerContract;
use App\Models\ManufacturerDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManufacturerController extends Controller
{
    // ------------------------------------------------------- Manufacturer ----

    public function createManufacturer(Request $request)
    {
        $userData = $this->getUserLogin();
        $manufacturer = new Manufacturer();
        $manufacturer->name = $request->name;
        $manufacturer->website = $request->website;
        $manufacturer->email = $request->email;
        $manufacturer->phone_number = $request->phone_number;
        $manufacturer->country_id = $request->country_id;
        $manufacturer->address = $request->address;
        $manufacturer->city_id = $request->city_id;
        $manufacturer->payment_term_id = $request->payment_term_id ?: null;
        $manufacturer->credit_limit = (float) ($request->credit_limit ?? 0);
        $manufacturer->tin = $request->tin;
        $manufacturer->institution_id = $userData->institution_id;
        $manufacturer->branch_id = $userData->branch_id;
        $manufacturer->user_id = $userData->id;
        $manufacturer->description = $request->description;
        $manufacturer->status = $request->status ?: 'Active';
        $manufacturer->created_by = $userData->id;
        $manufacturer->created_on = now();
        $manufacturer->save();

        return $this->genericResponse(true, "Manufacturer created successfully", 201, $manufacturer, "createManufacturer", $request);
    }

    public function updateManufacturer(Request $request)
    {
        $userData = $this->getUserLogin();
        $manufacturer = Manufacturer::where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!$manufacturer) {
            return $this->genericResponse(false, "Manufacturer not found", 400, null, "updateManufacturer", $request);
        }
        foreach (['name', 'website', 'email', 'phone_number', 'country_id', 'address', 'city_id', 'tin', 'description'] as $f) {
            if ($request->has($f)) {
                $manufacturer->$f = $request->$f;
            }
        }
        $manufacturer->payment_term_id = $request->payment_term_id ?: $manufacturer->payment_term_id;
        if ($request->has('credit_limit')) {
            $manufacturer->credit_limit = (float) $request->credit_limit;
        }
        if ($request->status) {
            $manufacturer->status = $request->status;
        }
        $manufacturer->updated_by = $userData->id;
        $manufacturer->updated_on = now();
        $manufacturer->save();

        return $this->genericResponse(true, "Manufacturer updated successfully", 200, $manufacturer, "updateManufacturer", $request);
    }

    public function getManufacturers()
    {
        $userData = $this->getUserLogin();
        $rows = DB::table('manufacturers as m')
            ->leftJoin('payment_terms as pt', 'pt.id', '=', 'm.payment_term_id')
            ->where(['m.status' => 'Active', 'm.institution_id' => $userData->institution_id])
            ->select('m.*', 'pt.name as payment_term')
            ->orderBy('m.name', 'asc')->get();
        $rows->transform(function ($m) use ($userData) {
            $m->outstanding = (float) DB::table('payables')
                ->where(['manufacturer_id' => $m->id, 'status' => 'Active', 'institution_id' => $userData->institution_id])
                ->sum(DB::raw('amount - amount_paid'));
            return $m;
        });
        return $this->genericResponse(true, "Manufacturers fetched successfully", 200, $rows, "getManufacturers", []);
    }

    public function getManufacturerProfile(Request $request)
    {
        $userData = $this->getUserLogin();
        $inst = (int) $userData->institution_id;
        $manufacturer = Manufacturer::with(['paymentTerm', 'contacts', 'contracts', 'documents'])
            ->where(['id' => $request->id, 'institution_id' => $inst])->first();
        if (!$manufacturer) {
            return $this->genericResponse(false, "Manufacturer not found", 400, null, "getManufacturerProfile", $request);
        }
        $mid = (int) $manufacturer->id;

        $avg = DB::table('manufacturer_ratings')->where('manufacturer_id', $mid)
            ->selectRaw('COUNT(*) c, COALESCE(AVG(quality),0) quality, COALESCE(AVG(delivery),0) delivery,
                COALESCE(AVG(price),0) price, COALESCE(AVG(communication),0) communication, COALESCE(AVG(overall),0) overall')->first();

        $productCount = (int) DB::table('products')->where(['manufacturer_id' => $mid, 'institution_id' => $inst])->count();
        $totalPurchased = (float) DB::table('stock_histories as h')
            ->join('products as p', 'p.id', '=', 'h.product_id')
            ->where('p.manufacturer_id', $mid)->where('h.institution_id', $inst)
            ->sum(DB::raw('h.quantity * h.purchase_price'));
        $outstanding = (float) DB::table('payables')
            ->where(['manufacturer_id' => $mid, 'status' => 'Active', 'institution_id' => $inst])
            ->sum(DB::raw('amount - amount_paid'));

        return $this->genericResponse(true, "Manufacturer profile fetched successfully", 200, [
            'manufacturer' => $manufacturer,
            'ratings_summary' => $avg,
            'summary' => [
                'products' => $productCount,
                'total_purchased' => $totalPurchased,
                'outstanding' => $outstanding,
            ],
        ], "getManufacturerProfile", $request);
    }

    public function getManufacturerProducts(Request $request)
    {
        $userData = $this->getUserLogin();
        $rows = DB::table('products as p')
            ->leftJoin('stocks as s', 's.product_id', '=', 'p.id')
            ->leftJoin('product_categories as c', 'c.id', '=', 'p.category_id')
            ->where(['p.manufacturer_id' => $request->id, 'p.institution_id' => $userData->institution_id])
            ->select('p.id', 'p.name', 'p.ref_no', 'c.name as category', 's.quantity', 's.purchase_price', 's.selling_price')
            ->orderBy('p.name')->get();
        return $this->genericResponse(true, "Manufacturer products fetched successfully", 200, $rows, "getManufacturerProducts", $request);
    }

    public function getManufacturerDeliveries(Request $request)
    {
        $userData = $this->getUserLogin();
        $inst = (int) $userData->institution_id;
        $deliveries = DB::table('stock_histories as h')
            ->join('products as p', 'p.id', '=', 'h.product_id')
            ->where('p.manufacturer_id', $request->id)->where('h.institution_id', $inst)
            ->select('h.id', 'h.stock_date', 'p.name as product', 'h.quantity', 'h.purchase_price',
                DB::raw('(h.quantity * h.purchase_price) as cost'))
            ->orderByDesc('h.stock_date')->limit(200)->get();
        $payables = DB::table('payables')
            ->where(['manufacturer_id' => $request->id, 'institution_id' => $inst])
            ->select('id', 'ref_no', 'amount', 'amount_paid', 'status', 'created_on',
                DB::raw('(amount - amount_paid) as outstanding'))
            ->orderByDesc('id')->limit(200)->get();
        return $this->genericResponse(true, "Deliveries fetched successfully", 200, [
            'deliveries' => $deliveries, 'payables' => $payables,
        ], "getManufacturerDeliveries", $request);
    }

    // ------------------------------------------------------------ Contacts ---

    public function createManufacturerContact(Request $request)
    {
        $userData = $this->getUserLogin();
        if (empty($request->manufacturer_id) || empty($request->name)) {
            return $this->genericResponse(false, "Manufacturer and contact name are required", 400, null, "createManufacturerContact", $request);
        }
        if ($request->is_primary) {
            ManufacturerContact::where('manufacturer_id', $request->manufacturer_id)->update(['is_primary' => false]);
        }
        $contact = ManufacturerContact::create([
            'manufacturer_id' => $request->manufacturer_id, 'name' => $request->name, 'title' => $request->title,
            'email' => $request->email, 'phone' => $request->phone, 'is_primary' => (bool) $request->is_primary,
            'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
            'status' => 'Active', 'created_by' => $userData->id, 'created_on' => now(),
        ]);
        return $this->genericResponse(true, "Contact added successfully", 201, $contact, "createManufacturerContact", $request);
    }

    public function getManufacturerContacts(Request $request)
    {
        $contacts = ManufacturerContact::where(['manufacturer_id' => $request->manufacturer_id, 'status' => 'Active'])->orderByDesc('is_primary')->get();
        return $this->genericResponse(true, "Contacts fetched successfully", 200, $contacts, "getManufacturerContacts", $request);
    }

    public function deleteManufacturerContact(Request $request)
    {
        ManufacturerContact::where('id', $request->id)->delete();
        return $this->genericResponse(true, "Contact removed successfully", 200, null, "deleteManufacturerContact", $request);
    }

    // ------------------------------------------------------------- Ratings ---

    public function addManufacturerRating(Request $request)
    {
        $userData = $this->getUserLogin();
        $q = (float) $request->quality;
        $d = (float) $request->delivery;
        $p = (float) $request->price;
        $c = (float) $request->communication;
        if (empty($request->manufacturer_id) || $q <= 0 || $d <= 0 || $p <= 0 || $c <= 0) {
            return $this->genericResponse(false, "Rate all four criteria (1-5)", 400, null, "addManufacturerRating", $request);
        }
        $overall = round(($q + $d + $p + $c) / 4, 2);
        $rating = ManufacturerRating::create([
            'manufacturer_id' => $request->manufacturer_id, 'quality' => $q, 'delivery' => $d, 'price' => $p, 'communication' => $c,
            'overall' => $overall, 'comment' => $request->comment, 'rated_by' => $userData->id, 'rating_date' => now(),
            'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id, 'status' => 'Active', 'created_on' => now(),
        ]);
        $avg = (float) ManufacturerRating::where('manufacturer_id', $request->manufacturer_id)->avg('overall');
        Manufacturer::where('id', $request->manufacturer_id)->update(['rating' => round($avg, 2)]);

        return $this->genericResponse(true, "Rating added successfully", 201, $rating, "addManufacturerRating", $request);
    }

    public function getManufacturerRatings(Request $request)
    {
        $ratings = DB::table('manufacturer_ratings as r')
            ->leftJoin('users as u', 'u.id', '=', 'r.rated_by')
            ->where('r.manufacturer_id', $request->manufacturer_id)
            ->select('r.*', DB::raw("CONCAT(u.first_name,' ',u.last_name) as rated_by_name"))
            ->orderByDesc('r.id')->get();
        return $this->genericResponse(true, "Ratings fetched successfully", 200, $ratings, "getManufacturerRatings", $request);
    }

    // ----------------------------------------------------------- Contracts ---

    public function createManufacturerContract(Request $request)
    {
        $userData = $this->getUserLogin();
        if (empty($request->manufacturer_id) || empty($request->title)) {
            return $this->genericResponse(false, "Manufacturer and contract title are required", 400, null, "createManufacturerContract", $request);
        }
        $filePath = null;
        if (!empty($request->file)) {
            try {
                $filePath = $this->saveBase64File($request->file, 'manufacturers/contracts');
            } catch (\Throwable $th) {
                return $this->genericResponse(false, $th->getMessage(), 400, null, "createManufacturerContract", $request);
            }
        }
        $contract = ManufacturerContract::create([
            'manufacturer_id' => $request->manufacturer_id, 'title' => $request->title, 'contract_no' => $request->contract_no,
            'start_date' => $request->start_date ?: null, 'end_date' => $request->end_date ?: null,
            'value' => (float) ($request->value ?? 0), 'status' => $request->status ?: 'Active',
            'file_path' => $filePath, 'notes' => $request->notes,
            'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
            'created_by' => $userData->id, 'created_on' => now(),
        ]);
        return $this->genericResponse(true, "Contract saved successfully", 201, $contract, "createManufacturerContract", $request);
    }

    public function getManufacturerContracts(Request $request)
    {
        $contracts = ManufacturerContract::where('manufacturer_id', $request->manufacturer_id)->orderByDesc('id')->get();
        return $this->genericResponse(true, "Contracts fetched successfully", 200, $contracts, "getManufacturerContracts", $request);
    }

    public function deleteManufacturerContract(Request $request)
    {
        ManufacturerContract::where('id', $request->id)->delete();
        return $this->genericResponse(true, "Contract removed successfully", 200, null, "deleteManufacturerContract", $request);
    }

    // ----------------------------------------------------------- Documents ---

    public function createManufacturerDocument(Request $request)
    {
        $userData = $this->getUserLogin();
        if (empty($request->manufacturer_id) || empty($request->name)) {
            return $this->genericResponse(false, "Manufacturer and document name are required", 400, null, "createManufacturerDocument", $request);
        }
        $filePath = null;
        if (!empty($request->file)) {
            try {
                $filePath = $this->saveBase64File($request->file, 'manufacturers/documents');
            } catch (\Throwable $th) {
                return $this->genericResponse(false, $th->getMessage(), 400, null, "createManufacturerDocument", $request);
            }
        }
        $doc = ManufacturerDocument::create([
            'manufacturer_id' => $request->manufacturer_id, 'name' => $request->name, 'type' => $request->type,
            'file_path' => $filePath, 'expiry_date' => $request->expiry_date ?: null, 'status' => 'Active',
            'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
            'created_by' => $userData->id, 'created_on' => now(),
        ]);
        return $this->genericResponse(true, "Document saved successfully", 201, $doc, "createManufacturerDocument", $request);
    }

    public function getManufacturerDocuments(Request $request)
    {
        $docs = ManufacturerDocument::where('manufacturer_id', $request->manufacturer_id)->orderByDesc('id')->get();
        return $this->genericResponse(true, "Documents fetched successfully", 200, $docs, "getManufacturerDocuments", $request);
    }

    public function deleteManufacturerDocument(Request $request)
    {
        ManufacturerDocument::where('id', $request->id)->delete();
        return $this->genericResponse(true, "Document removed successfully", 200, null, "deleteManufacturerDocument", $request);
    }
}
