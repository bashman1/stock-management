<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\CustomerPrice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    // ------------------------------------------------------------ Customer ---

    public function createCustomer(Request $request)
    {
        $userData = $this->getUserLogin();
        $customer = new Customer();
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->phone_number = $request->phone_number;
        $customer->address = $request->address;
        $customer->status = $request->status ?: 'Active';
        $customer->description = $request->description;
        $customer->customer_group_id = $request->customer_group_id ?: null;
        $customer->credit_limit = (float) ($request->credit_limit ?? 0);
        $customer->discount_percent = $request->discount_percent !== null && $request->discount_percent !== '' ? (float) $request->discount_percent : null;
        $customer->payment_term_id = $request->payment_term_id ?: null;
        $customer->tin = $request->tin;
        $customer->user_id = $userData->id;
        $customer->branch_id = $userData->branch_id;
        $customer->institution_id = $userData->institution_id;
        $customer->created_by = $userData->id;
        $customer->created_on = Carbon::now();
        $customer->save();

        return $this->genericResponse(true, "Customer created successfully", 201, $customer, "createCustomer", $request);
    }

    public function updateCustomer(Request $request)
    {
        $userData = $this->getUserLogin();
        $customer = Customer::where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
        if (!$customer) {
            return $this->genericResponse(false, "Customer not found", 400, null, "updateCustomer", $request);
        }
        foreach (['name', 'email', 'phone_number', 'address', 'description', 'tin'] as $f) {
            if ($request->has($f)) {
                $customer->$f = $request->$f;
            }
        }
        $customer->customer_group_id = $request->customer_group_id ?: null;
        $customer->payment_term_id = $request->payment_term_id ?: null;
        if ($request->has('credit_limit')) {
            $customer->credit_limit = (float) $request->credit_limit;
        }
        if ($request->has('discount_percent')) {
            $customer->discount_percent = $request->discount_percent !== null && $request->discount_percent !== '' ? (float) $request->discount_percent : null;
        }
        if ($request->status) {
            $customer->status = $request->status;
        }
        $customer->updated_by = $userData->id;
        $customer->updated_on = now();
        $customer->save();

        return $this->genericResponse(true, "Customer updated successfully", 200, $customer, "updateCustomer", $request);
    }

    public function getCustomers(Request $request)
    {
        $userData = $this->getUserLogin();
        $rows = DB::table('customers as c')
            ->leftJoin('customer_groups as g', 'g.id', '=', 'c.customer_group_id')
            ->where('c.institution_id', $userData->institution_id)
            ->select('c.*', 'g.name as group_name', 'g.discount_percent as group_discount')
            ->orderBy('c.name')->get();
        $rows->transform(function ($c) use ($userData) {
            $c->outstanding = (float) DB::table('customer_receivables')
                ->where(['customer_id' => $c->id, 'status' => 'Active', 'institution_id' => $userData->institution_id])
                ->sum(DB::raw('amount - amount_paid'));
            $c->effective_discount = $c->discount_percent ?? $c->group_discount ?? 0;
            return $c;
        });
        return $this->genericResponse(true, "Customers fetched successfully", 200, $rows, "getCustomers", $request);
    }

    public function getCustomerProfile(Request $request)
    {
        $userData = $this->getUserLogin();
        $inst = (int) $userData->institution_id;
        $customer = Customer::with(['group', 'paymentTerm'])
            ->where(['id' => $request->id, 'institution_id' => $inst])->first();
        if (!$customer) {
            return $this->genericResponse(false, "Customer not found", 400, null, "getCustomerProfile", $request);
        }
        $cid = (int) $customer->id;

        $totalSales = (float) DB::table('orders')->where(['customer_id' => $cid, 'status' => 'Active', 'institution_id' => $inst])->sum('total');
        $ordersCount = (int) DB::table('orders')->where(['customer_id' => $cid, 'status' => 'Active', 'institution_id' => $inst])->count();
        $outstanding = (float) DB::table('customer_receivables')->where(['customer_id' => $cid, 'status' => 'Active', 'institution_id' => $inst])->sum(DB::raw('amount - amount_paid'));
        $effectiveDiscount = $customer->discount_percent ?? ($customer->group->discount_percent ?? 0);

        return $this->genericResponse(true, "Customer profile fetched successfully", 200, [
            'customer' => $customer,
            'effective_discount' => $effectiveDiscount,
            'summary' => [
                'total_sales' => $totalSales,
                'orders' => $ordersCount,
                'outstanding' => $outstanding,
                'credit_available' => max(0, (float) $customer->credit_limit - $outstanding),
            ],
        ], "getCustomerProfile", $request);
    }

    public function getCustomerSales(Request $request)
    {
        $userData = $this->getUserLogin();
        $orders = DB::table('orders')
            ->where(['customer_id' => $request->id, 'status' => 'Active', 'institution_id' => $userData->institution_id])
            ->select('id', 'ref_no', 'receipt_no', 'total', 'discount', 'amount_paid', 'payment_method', 'payment_status', 'tran_date', 'created_on')
            ->orderByDesc('id')->limit(300)->get();
        return $this->genericResponse(true, "Customer sales fetched successfully", 200, $orders, "getCustomerSales", $request);
    }

    public function getCustomerReceivables(Request $request)
    {
        $userData = $this->getUserLogin();
        $rows = DB::table('customer_receivables')
            ->where(['customer_id' => $request->id, 'institution_id' => $userData->institution_id])
            ->select('id', 'ref_no', 'receipt_no', 'amount', 'amount_paid', 'status', 'created_on',
                DB::raw('(amount - amount_paid) as outstanding'))
            ->orderByDesc('id')->limit(300)->get();
        return $this->genericResponse(true, "Customer receivables fetched successfully", 200, $rows, "getCustomerReceivables", $request);
    }

    // -------------------------------------------------------------- Pricing --

    /**
     * Upsert a price override for a product, scoped to either a customer or a
     * customer group (exactly one of customer_id / customer_group_id).
     */
    public function createCustomerPrice(Request $request)
    {
        $userData = $this->getUserLogin();
        $customerId = $request->customer_id ?: null;
        $groupId = $request->customer_group_id ?: null;
        if ((!$customerId && !$groupId) || empty($request->product_id) || $request->price === null) {
            return $this->genericResponse(false, "A customer or group, a product and a price are required", 400, null, "createCustomerPrice", $request);
        }
        // replace any existing override for the same scope + product
        CustomerPrice::where('product_id', $request->product_id)
            ->when($customerId, fn ($q) => $q->where('customer_id', $customerId))
            ->when($groupId, fn ($q) => $q->where('customer_group_id', $groupId))
            ->delete();

        $price = CustomerPrice::create([
            'customer_id' => $customerId, 'customer_group_id' => $groupId, 'product_id' => $request->product_id,
            'price' => (float) $request->price, 'institution_id' => $userData->institution_id, 'branch_id' => $userData->branch_id,
            'status' => 'Active', 'created_by' => $userData->id, 'created_on' => now(),
        ]);
        return $this->genericResponse(true, "Price saved successfully", 201, $price, "createCustomerPrice", $request);
    }

    public function getCustomerPrices(Request $request)
    {
        // returns the price overrides for a customer OR a group (with product names)
        $rows = DB::table('customer_prices as cp')
            ->join('products as p', 'p.id', '=', 'cp.product_id')
            ->when($request->customer_id, fn ($q) => $q->where('cp.customer_id', $request->customer_id))
            ->when($request->customer_group_id, fn ($q) => $q->where('cp.customer_group_id', $request->customer_group_id))
            ->where('cp.status', 'Active')
            ->select('cp.*', 'p.name as product_name', 'p.ref_no')
            ->orderBy('p.name')->get();
        return $this->genericResponse(true, "Prices fetched successfully", 200, $rows, "getCustomerPrices", $request);
    }

    public function deleteCustomerPrice(Request $request)
    {
        CustomerPrice::where('id', $request->id)->delete();
        return $this->genericResponse(true, "Price removed successfully", 200, null, "deleteCustomerPrice", $request);
    }

    /**
     * POS feed: the effective discount + merged price map for a customer
     * (group prices first, customer overrides on top).
     */
    public function getCustomerPricing(Request $request)
    {
        $customer = Customer::with('group')->find($request->customer_id);
        if (!$customer) {
            return $this->genericResponse(false, "Customer not found", 400, null, "getCustomerPricing", $request);
        }
        $prices = [];
        if ($customer->customer_group_id) {
            foreach (CustomerPrice::where(['customer_group_id' => $customer->customer_group_id, 'status' => 'Active'])->get() as $gp) {
                $prices[$gp->product_id] = (float) $gp->price;
            }
        }
        foreach (CustomerPrice::where(['customer_id' => $customer->id, 'status' => 'Active'])->get() as $cp) {
            $prices[$cp->product_id] = (float) $cp->price; // customer overrides group
        }
        $discount = $customer->discount_percent ?? ($customer->group->discount_percent ?? 0);
        return $this->genericResponse(true, "Customer pricing fetched successfully", 200, [
            'discount_percent' => (float) $discount,
            'prices' => (object) $prices,
        ], "getCustomerPricing", $request);
    }

    // --------------------------------------------------------------- Groups --

    public function createCustomerGroup(Request $request)
    {
        $userData = $this->getUserLogin();
        if (empty($request->name)) {
            return $this->genericResponse(false, "Group name is required", 400, null, "createCustomerGroup", $request);
        }
        if ($request->id) {
            $group = CustomerGroup::where(['id' => $request->id, 'institution_id' => $userData->institution_id])->first();
            if (!$group) {
                return $this->genericResponse(false, "Group not found", 400, null, "createCustomerGroup", $request);
            }
            $group->name = $request->name;
            $group->discount_percent = (float) ($request->discount_percent ?? 0);
            $group->description = $request->description;
            $group->updated_by = $userData->id;
            $group->updated_on = now();
            $group->save();
            return $this->genericResponse(true, "Group updated successfully", 200, $group, "createCustomerGroup", $request);
        }
        $group = CustomerGroup::create([
            'name' => $request->name, 'discount_percent' => (float) ($request->discount_percent ?? 0),
            'description' => $request->description, 'institution_id' => $userData->institution_id, 'status' => 'Active',
            'created_by' => $userData->id, 'created_on' => now(),
        ]);
        return $this->genericResponse(true, "Group created successfully", 201, $group, "createCustomerGroup", $request);
    }

    public function getCustomerGroups()
    {
        $userData = $this->getUserLogin();
        $groups = CustomerGroup::where(['status' => 'Active', 'institution_id' => $userData->institution_id])->orderBy('name')->get();
        return $this->genericResponse(true, "Customer groups fetched successfully", 200, $groups, "getCustomerGroups", []);
    }
}
