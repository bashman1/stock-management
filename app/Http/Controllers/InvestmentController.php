<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use App\Models\Investment;
use Illuminate\Http\Request;

class InvestmentController extends Controller
{
    public function createInvestment(Request $request)
    {
        try {
            $userData = $this->getUserLogin();
            $data = [
                'project_id' => $request->project_id,
                'member_id' => $request->member_id,
                'quantity' => $request->quantity,
                'description' => $request->description,
                'date' => $request->date,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ];

            $investment = Investment::create($data);
            return $this->genericResponse(true, 'Investment created successfully', 201, $investment, 'createInvestment', []);
        } catch (\Exception $e) {
            return $this->genericResponse(false, $e->getMessage(), 500, [], 'createInvestment', []);
        }
    }


    public function getInvestments()
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();
            $queryString = "SELECT I.id, I.project_id, I.member_id, I.quantity,
                I.date, I.status, P.name AS project_name, P.benefit, P.benefit_type,P.monitory,
                CONCAT(M.first_name,' ',M.last_name, ' ',M.other_name) AS customer, I.created_at, D.name AS duration
                FROM investments I
                INNER JOIN projects P  ON P.id = I.project_id
                INNER JOIN members M ON M.id = I.member_id
                INNER JOIN durations D ON D.id = P.duration_id
                WHERE I.status = 'Active' ";

            if ($isNotAdmin) {
                $queryString .= " AND I.institution_id = $userData->institution_id";
            }
            $queryString .= " ORDER BY I.id DESC ";
            $investments = DB::select($queryString);
            return $this->genericResponse(true, 'Investments retrieved successfully', 200, $investments, 'getInvestments', []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, [], 'getInvestments', []);
        }
    }
}
