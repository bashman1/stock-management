<?php

namespace App\Http\Controllers;

use App\Imports\MailReceiverImport;
use App\Models\MailReceiver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MailReceiverController extends Controller
{
    public function uploadMailReceivers(Request $request)
    {
        $path = $request->file;
        $response = Excel::import(new MailReceiverImport(), $path);
        return $this->genericResponse(true, "Mail receiver uploaded successfully", 201, $response, "uploadMailReceivers", $request);
    }


    // public function getMailReceivers(Request $request)
    // {
    //     try {
    //         $userData = $this->getUserLogin();
    //         $isNotAdmin = $this->isNotAdmin();

    //         // Get pagination parameters (default to 25 if not provided)
    //         $rowPerPage = $request->input('per_page', 25);
    //         $page = $request->input('page', 1);

    //         // Build the main query
    //         $queryString = "SELECT M.id, M.name, M.email, M.category, M.user_id, M.institution_id, M.branch_id,
    //         M.created_by, M.updated_by, M.created_on, M.updated_on, M.created_at, M.updated_at,
    //         CONCAT(U.first_name,' ',U.last_name,' ',U.other_name) AS user_name,
    //         I.name AS institution_name, B.name AS branch_name
    //         FROM mail_receivers M
    //         INNER JOIN users U ON U.id = M.user_id
    //         LEFT JOIN institutions I ON I.id = M.institution_id
    //         LEFT JOIN branches B ON B.id = M.branch_id";

    //         // Build count query for pagination
    //         $countSql = "SELECT COUNT(*) as total FROM mail_receivers M";

    //         if ($isNotAdmin) {
    //             $queryString .= " WHERE M.institution_id = $userData->institution_id";
    //             $countSql .= " WHERE M.institution_id = $userData->institution_id";
    //         } else {
    //             $queryString .= " WHERE 1=1";
    //             $countSql .= " WHERE 1=1";
    //         }

    //         // Add category filter if provided
    //         if ($request->has('category') && !empty($request->category)) {
    //             $category = $request->category;
    //             $queryString .= " AND M.category = '$category'";
    //             $countSql .= " AND M.category = '$category'";
    //         }

    //         // Add search filter if provided
    //         if ($request->has('search') && !empty($request->search)) {
    //             $search = $request->search;
    //             $queryString .= " AND (M.name LIKE '%$search%' OR M.email LIKE '%$search%')";
    //             $countSql .= " AND (M.name LIKE '%$search%' OR M.email LIKE '%$search%')";
    //         }

    //         $queryString .= " ORDER BY M.id DESC";

    //         // Get paginated results
    //         $mailReceivers = $this->paginate($rowPerPage, $countSql, $queryString, $page);

    //         // Get all unique categories for filtering (from filtered results)
    //         $categoryQuery = "SELECT DISTINCT category FROM mail_receivers M";
    //         if ($isNotAdmin) {
    //             $categoryQuery .= " WHERE M.institution_id = $userData->institution_id";
    //         } else {
    //             $categoryQuery .= " WHERE 1=1";
    //         }
    //         $categories = DB::select($categoryQuery);

    //         // Prepare response data
    //         $responseData = [
    //             'mail_receivers' => $mailReceivers,
    //             'categories' => array_column($categories, 'category')
    //         ];

    //         return $this->genericResponse(true, "Mail receivers fetched successfully", 200, $responseData, "getMailReceivers", $request);
    //     } catch (\Throwable $th) {
    //         return $this->genericResponse(false, $th->getMessage(), 500, null, "getMailReceivers", []);
    //     }
    // }


    public function getMailReceivers(Request $request)
    {
        try {
            $userData = $this->getUserLogin();
            $isNotAdmin = $this->isNotAdmin();

            $perPage = $request->input('per_page', 25);

            $query = MailReceiver::query()
                ->select(
                    'mail_receivers.*',
                    DB::raw("CONCAT(users.first_name,' ',users.last_name,' ',users.other_name) as user_name"),
                    'institutions.name as institution_name',
                    'branches.name as branch_name'
                )
                ->join('users', 'users.id', '=', 'mail_receivers.user_id')
                ->leftJoin('institutions', 'institutions.id', '=', 'mail_receivers.institution_id')
                ->leftJoin('branches', 'branches.id', '=', 'mail_receivers.branch_id');

            // Restrict non-admin users
            if ($isNotAdmin) {
                $query->where('mail_receivers.institution_id', $userData->institution_id);
            }

            // Category filter
            if ($request->filled('category')) {
                $query->where('mail_receivers.category', $request->category);
            }

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('mail_receivers.name', 'like', "%{$search}%")
                        ->orWhere('mail_receivers.email', 'like', "%{$search}%");
                });
            }

            // Paginate (automatic count + limit)
            $mailReceivers = $query
                ->orderByDesc('mail_receivers.id')
                ->paginate($perPage);

            // Categories (optimized)
            $categoriesQuery = MailReceiver::query()->select('category')->distinct();

            if ($isNotAdmin) {
                $categoriesQuery->where('institution_id', $userData->institution_id);
            }

            $categories = $categoriesQuery->pluck('category');

            return $this->genericResponse(true, "Mail receivers fetched successfully", 200, ['mail_receivers' => $mailReceivers, 'categories' => $categories], "getMailReceivers", $request);
        } catch (\Throwable $th) {
            return $this->genericResponse(false, $th->getMessage(), 500, null, "getMailReceivers", []);
        }
    }

    public function sendBulkMails(Request $request)
    {
        $request->validate([
            'subject' => 'required|string',
            'body' => 'required|string',
            'receivers' => 'nullable|array',
            'category' => 'nullable|boolean'
        ]);

        // Base query
        $query = MailReceiver::where('status', 'Active');

        // If category filter is enabled
        if ($request->category && !empty($request->receivers)) {
            $categories = collect($request->receivers)
                ->pluck('category')
                ->unique()
                ->toArray();

            if (!empty($categories)) {
                $query->whereIn('category', $categories);
            }
            // $query->whereIn('category', $categories);
        } else {
            $ids = collect($request->receivers)
                ->pluck('id')
                ->toArray();

            if (!empty($ids)) {
                $query->whereIn('id', $ids);
            }
            // $query->whereIn('id', $ids);
        }

        // Process in chunks to avoid memory issues
        $query->chunk(100, function ($receivers) use ($request) {

            foreach ($receivers as $receiver) {

                $message = str_replace('__name__', $receiver->name, $request->body);

                $mail = [
                    "subject" => $request->subject,
                    "body" => $message,
                    "to" => [$receiver->email],
                    "cc" => [],
                    "bcc" => [],
                    "has_attachment" => false,
                    "created_on" => now(),
                ];
                // Dispatch job instead of sending directly
                $this->sendMail($mail);
            }
        });

        return $this->genericResponse(true, "Bulk messages queued successfully", 201, [], "sendBulkMails", $request);
    }
}
