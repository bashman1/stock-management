<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\SystemLog;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SystemLogController extends Controller
{

    public function getSystemLogs(Request $request)
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();

            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $perPage = (int) ($request->query('perPage') ?: 15);
            if ($perPage < 1 || $perPage > 100) {
                $perPage = 15;
            }
            $offset = ($currentPage - 1) * $perPage;

            // Build a parameterized WHERE clause shared by the data + count queries.
            $where = [];
            $bindings = [];
            if ($isNotAdmin) {
                $where[] = "S.institution_id = ?";
                $bindings[] = $userData->institution_id;
            }
            if ($request->filled('from_date')) {
                $where[] = "S.created_at::date >= ?";
                $bindings[] = $request->query('from_date');
            }
            if ($request->filled('to_date')) {
                $where[] = "S.created_at::date <= ?";
                $bindings[] = $request->query('to_date');
            }
            if ($request->filled('action')) {
                $where[] = "S.action ILIKE ?";
                $bindings[] = '%' . $request->query('action') . '%';
            }
            if ($request->filled('severity')) {
                $where[] = "S.severity = ?";
                $bindings[] = $request->query('severity');
            }
            if ($request->filled('event_category')) {
                $where[] = "S.event_category = ?";
                $bindings[] = $request->query('event_category');
            }
            if ($request->query('return_status') !== null && $request->query('return_status') !== '') {
                $where[] = "S.return_status = ?";
                $bindings[] = filter_var($request->query('return_status'), FILTER_VALIDATE_BOOLEAN);
            }
            if ($request->filled('search')) {
                $s = '%' . $request->query('search') . '%';
                $where[] = "(S.action ILIKE ? OR S.return_message ILIKE ? OR S.ip ILIKE ?
                    OR S.entity_type ILIKE ? OR CONCAT(U.first_name, ' ', U.last_name) ILIKE ?)";
                array_push($bindings, $s, $s, $s, $s, $s);
            }
            $whereSql = count($where) ? (' WHERE ' . implode(' AND ', $where)) : '';

            $queryString = "SELECT S.id, S.uuid, S.action, S.entity_type, S.entity_id, S.ip, S.user_agent, S.method, S.route,
            S.http_code, S.severity, S.event_category, S.return_message, S.return_status,
            S.status, S.user_id, S.institution_id, S.branch_id, S.created_on, S.created_at,
            CONCAT(U.first_name, ' ', U.last_name, ' ', COALESCE(U.other_name, '')) AS user,
            U.user_type, U.user_category, R.name AS role, I.name AS institution, B.name AS branch
            FROM system_logs S
            LEFT JOIN users U ON U.id = S.user_id
            LEFT JOIN roles R ON R.id = U.role_id
            LEFT JOIN institutions I ON I.id = S.institution_id
            LEFT JOIN branches B ON B.id = S.branch_id"
            . $whereSql
            . " ORDER BY S.id DESC LIMIT ? OFFSET ?";

            $logs = DB::select($queryString, array_merge($bindings, [$perPage, $offset]));

            $countQuery = "SELECT COUNT(*) as total FROM system_logs S
            LEFT JOIN users U ON U.id = S.user_id" . $whereSql;
            $totalLogs = DB::selectOne($countQuery, $bindings)->total;

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


    // public function getSystemLogs(){
    //     try {
    //         Log::info("Fetching audit logs");
    //         $userData = auth()->user();
    //         $isNotAdmin = $this->isNotAdmin();

    //         // Number of items per page
    //         $perPage = 15;

    //         // Fetch logs with pagination
    //         $query = DB::table('system_logs as S')
    //             ->select(
    //                 'S.id', 'S.action', 'S.ip', 'S.http_code', 'S.return_message',
    //                 'S.return_status', 'S.status',
    //                 'S.user_id', 'S.institution_id', 'S.branch_id', 'S.created_by',
    //                 'S.updated_by', 'S.created_on', 'S.updated_on', 'S.created_at', 'S.updated_at',
    //                 'U.first_name',  'U.last_name',  'U.other_name',
    //                 'U.user_type', 'U.user_category', 'R.name AS role',
    //                 'I.name AS institution', 'B.name AS branch'
    //             )
    //             ->leftJoin('users as U', 'U.id', '=', 'S.user_id')
    //             ->leftJoin('roles as R', 'R.id', '=', 'U.role_id')
    //             ->leftJoin('institutions as I', 'I.id', '=', 'S.institution_id')
    //             ->leftJoin('branches as B', 'B.id', '=', 'S.branch_id')
    //             ->when($isNotAdmin, fn($query) => $query->where('S.institution_id', $userData->institution_id))
    //             ->orderByDesc('S.id');

    //         $logs = $query->paginate($perPage);

    //         Log::info("Audit logs fetched successfully");
    //         return $this->genericResponse(true, "Audit logs fetched successfully", 200, $logs, "getSystemLogs", []);
    //     } catch (\Throwable $th) {
    //         Log::error("Error fetching audit logs: " . $th->getMessage());
    //         return $this->genericResponse(false, $th->getMessage(), 500, [], "getSystemLogs", []);
    //     }

    // }
}
