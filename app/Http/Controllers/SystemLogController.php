<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class SystemLogController extends Controller
{

    public function getSystemLogs()
    {
        try {
            $userData = auth()->user();
            $isNotAdmin = $this->isNotAdmin();

            // Current page number
            $currentPage = LengthAwarePaginator::resolveCurrentPage();

            // Number of items per page
            $perPage = 15;

            // Calculate the offset
            $offset = ($currentPage - 1) * $perPage;

            // Base query
            $queryString = "SELECT S.id, S.action, S.ip, S.http_code, S.return_message, S.return_status,
            S.request, S.response, S.status, S.user_id, S.institution_id, S.branch_id,
            S.created_by, S.updated_by, S.created_on, S.updated_on, S.created_at,
            S.updated_at, CONCAT(U.first_name, ' ', U.last_name, ' ', COALESCE(U.other_name, '')) AS user,
            U.user_type, U.user_category, R.name AS role, I.name AS institution,
            B.name AS branch
            FROM system_logs S
            LEFT JOIN users U ON U.id = S.user_id
            LEFT JOIN roles R ON R.id = U.role_id
            LEFT JOIN institutions I ON I.id = S.institution_id
            LEFT JOIN branches B ON B.id = S.branch_id";

            if ($isNotAdmin) {
                $queryString .= " WHERE S.institution_id = " . $userData->institution_id;
            }

            $queryString .= " ORDER BY S.id DESC LIMIT $perPage OFFSET $offset";

            // Fetch the logs for the current page
            $logs = DB::select($queryString);

            // Get the total count without limit/offset
            $countQuery = "SELECT COUNT(*) as total FROM system_logs S";

            if ($isNotAdmin) {
                $countQuery .= " WHERE S.institution_id = " . $userData->institution_id;
            }

            $totalLogs = DB::selectOne($countQuery)->total;

            // Create the paginator
            $paginatedLogs = new LengthAwarePaginator(
                $logs,
                $totalLogs,
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'query' => request()->query()]
            );

            return $this->genericResponse(true, "Audit logs fetched successfully", 200, $paginatedLogs, "getSystemLogs", []);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, $th, "getSystemLogs", []);
        }
    }
}
