<?php

namespace App\Http\Controllers;

use App\Models\Duration;
use Illuminate\Support\Facades\DB;
use App\Models\Investment;
use App\Models\Member;
use App\Models\Project;
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
                'investment_duration' => $request->duration,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ];

            $project = Project::find($request->project_id);
            $member = Member::find($request->member_id);
            $duration = Duration::find($project->duration_id);

            $body = "Dear {$member->first_name},\n\n";
            $body .= "You have successfully invested in the project: {$project->name}.\n";
            $body .= "Investment Details:\n";
            $body .= "Project Name: {$project->name}\n";
            $body .= "Investment: " . ($request->quantity * $project->monitory) . "\n";
            $body .= "Benefit: {$project->benefit}\n";
            $body .= "Benefit Type: {$project->benefit_type}\n";
            $body .= "Duration: {$duration->name}\n";
            $body .= "Date: {$request->date}\n";
            $body .= "Thank you for your investment.\n\n";
            $body .= "Best regards,\n";
            $body .= "Investment Team";

            // sending email
            $sendMail = [
                "subject" => "Investment",
                "body" => $body,
                "has_attachment" => false,
                "to" => $member->email,
                "cc" => [],
                "bcc" => [],
                "attachment" => "",
                "created_on" => now(),
                "attachment_name" => "",
            ];

            $this->sendMail($sendMail);

            $investment = Investment::create($data);
            return $this->genericResponse(true, 'Investment created successfully', 201, $investment, 'createInvestment', []);
        } catch (\Exception $e) {
            return $this->genericResponse(false, $e->getMessage(), 500, [], 'createInvestment', []);
        }
    }


    // public function getInvestments()
    // {
    //     try {
    //         $userData = $this->getUserLogin();
    //         $isNotAdmin = $this->isNotAdmin();
    //         $queryString = "SELECT I.id, I.project_id, I.member_id, I.quantity, I.investment_duration,
    //             I.date, I.status, P.name AS project_name, P.benefit, P.benefit_type,P.monitory,
    //             CONCAT(M.first_name,' ',M.last_name, ' ',M.other_name) AS customer, I.created_at, D.name AS duration
    //             FROM investments I
    //             INNER JOIN projects P  ON P.id = I.project_id
    //             INNER JOIN members M ON M.id = I.member_id
    //             INNER JOIN durations D ON D.id = P.duration_id
    //              ";

    //         $conditions = [];

    //         $conditions[] = "I.status = '" . addslashes('Active') . "'";


    //         if ($isNotAdmin) {
    //             // $queryString .= " AND I.institution_id = $userData->institution_id";
    //             $conditions[] = "I.institution_id = " . intval($userData->institution_id);
    //         }

    //         if (count($conditions) > 0) {
    //             $queryString .= " WHERE " . implode(" AND ", $conditions);
    //         }

    //         $queryString .= " ORDER BY I.id DESC ";
    //         $investments = DB::select($queryString);
    //         return $this->genericResponse(true, 'Investments retrieved successfully', 200, $investments, 'getInvestments', []);
    //     } catch (\Throwable $th) {
    //         return $this->genericResponse(false, $th->getMessage(), 500, [], 'getInvestments', []);
    //     }
    // }



    public function getInvestments(Request $request)
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();
            $queryString = "SELECT I.id, I.project_id, I.member_id, I.quantity, I.investment_duration,
                I.date, I.status, P.name AS project_name, P.benefit, P.benefit_type,P.monitory,
                CONCAT(M.first_name,' ',M.last_name, ' ',M.other_name) AS customer, I.created_at, D.name AS duration
                FROM investments I
                INNER JOIN projects P  ON P.id = I.project_id
                INNER JOIN members M ON M.id = I.member_id
                INNER JOIN durations D ON D.id = P.duration_id
                ";

            $conditions = [];
            if (isset($request->status)) {
                $conditions[] = "I.status = '" . addslashes($request->status) . "'";
            }

            if (isset($request->id)) {
                $conditions[] = "I.id = " . intval($request->id);
            }

            if (isset($request->member_id)) {
                $conditions[] = "I.member_id = " . intval($request->member_id);
            }

            if (isset($request->searchKey)) {
                $conditions[] = "(P.name ILIKE '%" . addslashes($request->searchKey) . "%' OR M.first_name ILIKE '%" . addslashes($request->searchKey) . "%' OR M.last_name ILIKE '%" . addslashes($request->searchKey) . "%')";
            }

            if ($isNotAdmin) {
                $conditions[] = "I.institution_id = " . intval($userData->institution_id);
            }

            if (count($conditions) > 0) {
                $queryString .= " WHERE " . implode(" AND ", $conditions);
            }

            $queryString .= " ORDER BY I.id DESC ";
            $investments = DB::select($queryString);
            return $this->genericResponse(true, 'Investments retrieved successfully', 200, $investments, 'getInvestments', []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, [], 'getInvestments', []);
        }
    }


    public function withdrawInvestment(Request $request){
        try {
            $userData = $this->getUserLogin();
            $investment = Investment::find($request->id);
            if (!$investment) {
                return $this->genericResponse(false, 'Investment not found', 404, [], 'withdrawInvestment', []);
            }

            $investment->status = 'Withdrawn';
            $investment->save();

            // Sending email notification
            $member = Member::find($investment->member_id);
            if ($member) {
                $body = "Dear {$member->first_name},\n\n";
                $body .= "Your investment in the project: {$investment->project_name} has been successfully withdrawn.\n";
                $body .= "Thank you for your participation.\n\n";
                $body .= "Best regards,\n";
                $body .= "Investment Team";

                $sendMail = [
                    "subject" => "Investment Withdrawal",
                    "body" => $body,
                    "has_attachment" => false,
                    "to" => $member->email,
                    "cc" => [],
                    "bcc" => [],
                    "attachment" => "",
                    "created_on" => now(),
                    "attachment_name" => "",
                ];

                if(filter_var($member->email, FILTER_VALIDATE_EMAIL)){
                    $this->sendMail($sendMail);
                }

            }

            return $this->genericResponse(true, 'Investment withdrawn successfully', 200, [], 'withdrawInvestment', []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, [], 'withdrawInvestment', []);
        }
    }
}
