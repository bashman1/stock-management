<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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

    public function getBuildings(Request $request){
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
}
