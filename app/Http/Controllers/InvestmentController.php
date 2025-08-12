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
