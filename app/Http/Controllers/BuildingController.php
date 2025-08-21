<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\BuildingUnit;
use App\Models\Rental;
use App\Models\RentalHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Models\Member;
use Carbon\Carbon;

class BuildingController extends Controller
{

    public function createBuilding(Request $request)
    {
        try {
            DB::beginTransaction();
            $userData = $this->getUserLogin();
            $building = Building::create([
                'name' => $request->name,
                'city_id' => $request->city_id,
                'street' => $request->street,
                'address' => $request->address,
                'plot' => $request->plot,
                'status' => $request->status ?? 'Active',
                'rent' => $request->rent ?? 0.00,
                'description' => $request->description,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ]);
            DB::commit();
            return $this->genericResponse(true, "Building created successfully", 201, $building, "createBuilding", $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "createBuilding", $request);
        }
    }

    public function getBuildings(Request $request)
    {
        try {

            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();

            $sqlString = "SELECT B.id, B.name, B.city_id, B.street, B.address, B.plot,
                B.status, B.description, B.institution_id, B.branch_id,
                B.created_by, B.created_on, B.created_at, C.name AS city_name,
                I.name AS institution_name, Q.name AS branch_name
                FROM buildings B
                LEFT JOIN city_refs C ON C.id = B.city_id::INT8
                INNER JOIN institutions I ON I.id = B.institution_id
                INNER JOIN branches Q ON Q.id =B.branch_id
                WHERE B.status = 'Active' ";

            $totalSql = "SELECT COUNT(B.id) AS total
                FROM buildings B
                LEFT JOIN city_refs C ON C.id = B.city_id::INT8
                INNER JOIN institutions I ON I.id = B.institution_id
                INNER JOIN branches Q ON Q.id =B.branch_id
                WHERE B.status = 'Active' ";

            if ($isNotAdmin) {
                $sqlString .= " AND B.institution_id = {$userData->institution_id} AND B.branch_id = {$userData->branch_id}";
                $totalSql .= " AND B.institution_id = {$userData->institution_id} AND B.branch_id = {$userData->branch_id}";
            }
            if ($request->search) {
                $search = $request->search;
                $sqlString .= " AND (B.name ILIKE '%{$search}%' OR B.address ILIKE '%{$search}%')";
                $totalSql .= " AND (B.name ILIKE '%{$search}%' OR B.address ILIKE '%{$search}%')";
            }

            if ($request->id){
                $sqlString .= " AND B.id = {$request->id}";
                $totalSql .= " AND B.id = {$request->id}";
            }

            if (!empty(trim($request->startDate))) {
                $startDate = date('Y-m-d', strtotime(trim($request->startDate))); // only date part
                $sqlString .= " AND B.created_at::date >= '$startDate'";
                $totalSql .= " AND B.created_at::date >= '$startDate'";
            }
            if (!empty(trim($request->endDate))) {
                $endDate = date('Y-m-d', strtotime(trim($request->endDate))); // only date part
                $sqlString .= " AND B.created_at::date <= '$endDate'";
                $totalSql .= " AND B.created_at::date <= '$endDate'";
            }
            $sqlString .= " ORDER BY B.id DESC";

            $buildings = $this->paginate($request->rowPerPage, $totalSql, $sqlString);

            return $this->genericResponse(true, "Buildings retrieved successfully", 200, $buildings, "getBuildings", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getBuildings", $request);
        }
    }


    public function createBuildingUnit(Request $request)
    {
        try {
            DB::beginTransaction();
            $userData = $this->getUserLogin();
            $buildingUnit = BuildingUnit::create([
                'building_id' => $request->building_id,
                'unit_type' => 'Room',
                'unit_no' => $request->unit,
                'name' => $request->name,
                'rent' => $request->rent,
                'description' => $request->description,
                'created_by' => $userData->id,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_on' => now(),
            ]);
            DB::commit();
            return $this->genericResponse(true, "Building unit created successfully", 201, $buildingUnit, "createBuildingUnit", $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "createBuildingUnit", $request);
        }
    }


    public function getBuildingUnits(Request $request)
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();

            $sqlString = "SELECT BU.id, BU.building_id, BU.unit_type, BU.unit_no, BU.name, BU.rent,
                BU.description, BU.status, BU.created_by, BU.created_on, BU.created_at, B.name AS building_name
                FROM building_units BU
                INNER JOIN buildings B ON B.id = BU.building_id";

            $totalSql = "SELECT COUNT(BU.id) AS total
                FROM building_units BU
                INNER JOIN buildings B ON B.id = BU.building_id";

            $conditions = [];

            if ($request->status){
                $conditions[] = "BU.status = '" . addslashes($request->status) . "'";
            }

            if ($isNotAdmin) {
                $conditions[] = "BU.institution_id = " . intval($userData->institution_id);
                $conditions[] = "BU.branch_id = " . intval($userData->branch_id);

                // $sqlString .= " AND B.institution_id = {$userData->institution_id} AND B.branch_id = {$userData->branch_id}";
                // $totalSql .= " AND B.institution_id = {$userData->institution_id} AND B.branch_id = {$userData->branch_id}";
            }
            if ($request->search) {
                $search = $request->search;
                $conditions[] = "BU.name ILIKE '%" . addslashes($search) . "%' OR B.name ILIKE '%" . addslashes($search) . "%'";

                // $sqlString .= " AND (BU.name ILIKE '%{$search}%' OR B.name ILIKE '%{$search}%')";
                // $totalSql .= " AND (BU.name ILIKE '%{$search}%' OR B.name ILIKE '%{$search}%')";
            }

            if (!empty(trim($request->startDate))) {
                $startDate = date('Y-m-d', strtotime(trim($request->startDate))); // only date part
                $conditions[] = "BU.created_at::date >= '$startDate'";
                // $sqlString .= " AND BU.created_at::date >= '$startDate'";
                // $totalSql .= " AND BU.created_at::date >= '$startDate'";
            }
            if (!empty(trim($request->endDate))) {
                $endDate = date('Y-m-d', strtotime(trim($request->endDate))); // only date part
                $conditions[] = "BU.created_at::date <= '$endDate'";
                // $sqlString .= " AND BU.created_at::date <= '$endDate'";
                // $totalSql .= " AND BU.created_at::date <= '$endDate'";
            }

            if ($request->building_id) {
                $conditions[] = "BU.building_id = " . intval($request->building_id);
                // $sqlString .= " AND BU.building_id = {$request->building_id}";
                // $totalSql .= " AND BU.building_id = {$request->building_id}";
            }

            if (count($conditions) > 0) {
                $sqlString .= " WHERE " . implode(" AND ", $conditions);
                $totalSql .= " WHERE " . implode(" AND ", $conditions);
            }

            $sqlString .= " ORDER BY BU.id DESC";

            $buildingUnits = $this->paginate($request->rowPerPage, $totalSql, $sqlString);

            return $this->genericResponse(true, "Building units retrieved successfully", 200, $buildingUnits, "getBuildingUnits", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getBuildingUnits", $request);
        }
    }


    public function rentUnit(Request $request)
    {
        try {
            DB::beginTransaction();
            $userData = $this->getUserLogin();

            // $dueDate = $request->rental_date->addMonths($request->duration)->format('Y-m-d H:i:s');

            $dueDate = Carbon::parse($request->rental_date)->addMonths($request->duration)->format('Y-m-d H:i:s');

            $rental = Rental::create([
                'building_id' => $request->building_id,
                'unit_id' => $request->unit_id,
                'member_id' => $request->member_id,
                'duration' => $request->duration,
                'amount_paid' => $request->amount_paid,
                'rental_date' => $request->rental_date ? date('Y-m-d H:i:s', strtotime($request->rental_date)) : now(),
                'due_date' => $dueDate ? date('Y-m-d H:i:s', strtotime($dueDate)) : null,
                'paid_date' => $request->rental_date ? date('Y-m-d H:i:s', strtotime($request->rental_date)) : null,
                'description' => $request->description,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
                'payment_status' => $request->payment_status ?? 'UNPAID',
            ]);


            $unit = BuildingUnit::find($request->unit_id);
            if ($unit) {
                $unit->status = 'Occupied';
                $unit->save();
            } else {
                return $this->genericResponse(false, "Building unit not found", 404, null, "rentUnit", $request);
            }

            // $rental->rental_id = $rental->id;
            $history = RentalHistory::create([
                'building_id' => $request->building_id,
                'unit_id' => $request->unit_id,
                'rental_id'=> $rental->id,
                'member_id' => $request->member_id,
                'duration' => $request->duration,
                'amount_paid' => $request->amount_paid,
                'rental_date' => $request->rental_date ? date('Y-m-d H:i:s', strtotime($request->rental_date)) : now(),
                'due_date' => $dueDate ? date('Y-m-d H:i:s', strtotime($dueDate)) : null,
                'paid_date' => $request->rental_date ? date('Y-m-d H:i:s', strtotime($request->rental_date)) : null,
                'description' => $request->description,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
                'payment_status' => $request->payment_status ?? 'UNPAID',
            ]);

            $member = Member::find($request->member_id);
            $branch = Branch::where(['id' => $userData->branch_id, 'institution_id' => $userData->institution_id])->first();
            $cash = $this->getCntParameterByCd('CL', $userData->institution_id, $branch->code);
            $sales = $this->getCntParameterByCd('SL', $userData->institution_id, $branch->code);

            $tran = (array)[
                "acct_no" => $cash->acct_no,
                "acct_type" => $cash->acct_type,
                "contra_acct_no" => $sales->acct_no,
                "contra_acct_type" => $sales->acct_type,
                "description" => "Rent payment:- {$member->first_name} {$member->last_name} on " . date('Y-m-d H:i:s'),
                "dr_cr_ind" => "DR/CR",
                "tran_amount" => $request->amount_paid,
                "reversal_flag" => 'N',
                "tran_date" => isset($request->date) ? $request->date : now(),
                "tran_cd" => 'RNT',
                "tran_id" => $this->generateUuid(),
                "status" => 'Active',
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "created_on" => now(),
                "member_id" =>  $request->member_id,
                // "product_id" => $product->id,
                // "stock_id" => $stock->id,
                "ext_ref" => $rental->id,
                "tran" => "RENT PAYMENT",
                // "reversal_reason"=>null,
                // "reversed_by"=>null,
                // "reversed_on"=>null,
            ];


            $postTran = $this->postTransaction($tran);

            $debitRequest = (object)[
                "acct_no" => $cash->acct_no,
                "acct_type" => $cash->acct_type,
                "tran_amt" => $postTran->tran_amount ?? 0,
                "reversal_flag" => 'N',
                "description" => $postTran->description ?? '',
                "transaction_date" => $postTran->tran_date ?? now(),
                "contra_acct_no" => $sales->acct_no,
                "contra_acct_type" => $sales->acct_type,
                "tran_type" => 'RENT PAYMENT',
                "tran_cd" => 'RNT',
                "tran_id" => $postTran->tran_id ?? null,
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "status" => 'Active',
            ];
            $creditRequest = (object)[
                "acct_no" => $sales->acct_no,
                "acct_type" => $sales->acct_type,
                "tran_amt" => $postTran->tran_amount ?? 0,
                "reversal_flag" => 'N',
                "description" => $postTran->description ?? '',
                "transaction_date" => $postTran->tran_date ?? now(),
                "contra_acct_no" => $cash->acct_no,
                "contra_acct_type" => $cash->acct_type,
                "tran_type" => 'STOCK IN',
                "tran_cd" => 'STO',
                "tran_id" => $postTran->tran_id ?? null,
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "status" => 'Active',
            ];

            $debit = $this->postGlDR($debitRequest);
            $credit = $this->postGlCR($creditRequest);


            DB::commit();
            return $this->genericResponse(true, "Building unit rented successfully", 200, $rental, "rentUnit", $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "rentUnit", $request);
        }
    }


    public function evacuateUnit(Request $request){
        $rental = Rental::find($request->id);
        if ($rental){
            $rental->delete();
            $rental->save();
        }
    }


    public function getRentals(Request $request)
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();

            $sqlString = "SELECT R.id, R.building_id, R.unit_id, R.member_id, R.duration, R.amount_paid,
                R.status, R.rental_date, R.due_date, R.paid_date, R.description, R.payment_status,
                R.created_at, B.name AS building_name, B.rent AS building_rent,
                CONCAT(U.name,' ',U.unit_no) AS unit, U.rent AS unit_rent, I.name AS institution_name,
                B.name AS branch_name, CONCAT(X.first_name,' ',X.last_name,' ',X.other_name) AS user_name,
                CONCAT(M.first_name,' ',M.last_name,' ',M.other_name) AS tenant_name
                FROM rentals R
                INNER JOIN buildings B ON B.id = R.building_id
                INNER JOIN building_units U ON U.id = R.unit_id
                INNER JOIN institutions I ON I.id = R.institution_id
                INNER JOIN branches Q ON Q.id = R.branch_id
                INNER JOIN users X ON X.id = R.created_by
                INNER JOIN members M ON M.id = R.member_id
                WHERE R.status = 'Active' ";

            $totalSql = "SELECT COUNT(R.id) AS total
                FROM rentals R
                INNER JOIN buildings B ON B.id = R.building_id
                INNER JOIN building_units U ON U.id = R.unit_id
                INNER JOIN institutions I ON I.id = R.institution_id
                INNER JOIN branches Q ON Q.id = R.branch_id
                INNER JOIN users X ON X.id = R.created_by
                INNER JOIN members M ON M.id = R.member_id
                WHERE R.status = 'Active' ";

            if ($isNotAdmin) {
                $sqlString .= " AND B.institution_id = {$userData->institution_id} AND B.branch_id = {$userData->branch_id}";
                $totalSql .= " AND B.institution_id = {$userData->institution_id} AND B.branch_id = {$userData->branch_id}";
            }
            if ($request->search) {
                $search = $request->search;
                $sqlString .= " AND (B.name ILIKE '%{$search}%' OR U.unit_no ILIKE '%{$search}%')";
                $totalSql .= " AND (B.name ILIKE '%{$search}%' OR U.unit_no ILIKE '%{$search}%')";
            }

            if (!empty(trim($request->startDate))) {
                $startDate = date('Y-m-d', strtotime(trim($request->startDate))); // only date part
                $sqlString .= " AND R.created_at::date >= '$startDate'";
                $totalSql .= " AND R.created_at::date >= '$startDate'";
            }
            if (!empty(trim($request->endDate))) {
                $endDate = date('Y-m-d', strtotime(trim($request->endDate))); // only date part
                $sqlString .= " AND R.created_at::date <= '$endDate'";
                $totalSql .= " AND R.created_at::date <= '$endDate'";
            }

            $sqlString .= " ORDER BY R.id DESC";

            $rentals = $this->paginate($request->rowPerPage, $totalSql, $sqlString);

            return $this->genericResponse(true, "Building units retrieved successfully", 200, $rentals, "getRentals", $request);
        } catch (\Throwable $th) {
            // DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getRentals", $request);
        }
    }


    public function makeRentPayment(Request $request)
    {
        try {
            DB::beginTransaction();
            $userData = $this->getUserLogin();

            $rental = Rental::find($request->id);
            if (!$rental) {
                return $this->genericResponse(false, "Rental not found", 404, null, "makeRentPayment", $request);
            }

            $unit = BuildingUnit::find($rental->unit_id);

            $numberOfMoths = ceil((double) $request->amount_paid / (double) $unit->rent);
            $dueDate = Carbon::parse($rental->rental_date)->addMonths($numberOfMoths)->format('Y-m-d H:i:s');

            $rental->amount_paid =  $rental->amount_paid + (double) $request->amount_paid;
            $rental->paid_date = now();
            $rental->due_date = $dueDate;
            $rental->duration = $rental->duration + $numberOfMoths;
            // $rental->payment_status = 'PAID';
            $rental->save();


            $member = Member::find($rental->member_id);
            $branch = Branch::where(['id' => $userData->branch_id, 'institution_id' => $userData->institution_id])->first();
            $cash = $this->getCntParameterByCd('CL', $userData->institution_id, $branch->code);
            $sales = $this->getCntParameterByCd('SL', $userData->institution_id, $branch->code);

            $tran = (array)[
                "acct_no" => $cash->acct_no,
                "acct_type" => $cash->acct_type,
                "contra_acct_no" => $sales->acct_no,
                "contra_acct_type" => $sales->acct_type,
                "description" => "Rent payment:- {$member->first_name} {$member->last_name} on " . date('Y-m-d H:i:s'),
                "dr_cr_ind" => "DR/CR",
                "tran_amount" => $request->amount_paid,
                "reversal_flag" => 'N',
                "tran_date" => isset($request->date) ? $request->date : now(),
                "tran_cd" => 'RNT',
                "tran_id" => $this->generateUuid(),
                "status" => 'Active',
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "created_on" => now(),
                "member_id" =>  $request->member_id,
                // "product_id" => $product->id,
                // "stock_id" => $stock->id,
                "ext_ref" => $rental->id,
                "tran" => "RENT PAYMENT",
                // "reversal_reason"=>null,
                // "reversed_by"=>null,
                // "reversed_on"=>null,
            ];


            $postTran = $this->postTransaction($tran);

            $debitRequest = (object)[
                "acct_no" => $cash->acct_no,
                "acct_type" => $cash->acct_type,
                "tran_amt" => $postTran->tran_amount ?? 0,
                "reversal_flag" => 'N',
                "description" => $postTran->description ?? '',
                "transaction_date" => $postTran->tran_date ?? now(),
                "contra_acct_no" => $sales->acct_no,
                "contra_acct_type" => $sales->acct_type,
                "tran_type" => 'RENT PAYMENT',
                "tran_cd" => 'RNT',
                "tran_id" => $postTran->tran_id ?? null,
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "status" => 'Active',
            ];
            $creditRequest = (object)[
                "acct_no" => $sales->acct_no,
                "acct_type" => $sales->acct_type,
                "tran_amt" => $postTran->tran_amount ?? 0,
                "reversal_flag" => 'N',
                "description" => $postTran->description ?? '',
                "transaction_date" => $postTran->tran_date ?? now(),
                "contra_acct_no" => $cash->acct_no,
                "contra_acct_type" => $cash->acct_type,
                "tran_type" => 'STOCK IN',
                "tran_cd" => 'STO',
                "tran_id" => $postTran->tran_id ?? null,
                "institution_id" => $userData->institution_id,
                "branch_id" => $userData->branch_id,
                "created_by" => $userData->id,
                "status" => 'Active',
            ];

            $debit = $this->postGlDR($debitRequest);
            $credit = $this->postGlCR($creditRequest);

            DB::commit();
            return $this->genericResponse(true, "Rent payment made successfully", 200, $rental, "makeRentPayment", $request);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "makeRentPayment", $request);
        }

    }
}
