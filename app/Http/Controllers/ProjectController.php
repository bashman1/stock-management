<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function createProject(Request $request)
    {
        try {
            $userData = $this->getUserLogin();
            $data = [
                'name' => $request->name,
                'description' => $request->description,
                'duration_id' => $request->duration_id,
                'benefit_type' => $request->benefit_type,
                'benefit' => $request->benefit,
                'monitory' => $request->monitory,
                'date' => $request->date,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ];

            $project = Project::create($data);
            return $this->genericResponse(true, 'Project created successfully', 201, $project, 'createProject', []);
        } catch (\Exception $e) {
            return $this->genericResponse(false, $e->getMessage(), 500, [], 'createProject', []);
        }
    }

    public function getProjects(){
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();
            $conditions = array();
            if ($isNotAdmin) {
                $conditions[] = ['institution_id', '=', $userData->institution_id];
                // $conditions[] = ['branch_id', '=', $userData->branch_id];
            }
            $conditions[] = ['status', '=', 'Active'];

            $projects = Project::with(['institution', 'branch', 'duration'])
                ->where($conditions)
                ->orderBy('id', 'asc');
            return $this->genericResponse(true, 'Projects retrieved successfully', 200, $projects->get(), 'getProjects', []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, [], 'getProjects', []);
        }

    }
}
