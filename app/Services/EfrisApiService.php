<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Models\EfrisOutBound;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class EfrisApiService
{
    protected string $baseUrl;
    protected string $bearerToken;

    public function __construct()
    {
        $this->baseUrl = config('services.efris.base_url');
        $this->bearerToken = config('services.efris.bearer_token');
    }

    /**
     * Generic EFRIS API request handler
     *
     * @param  string  $method   GET|POST
     * @param  string  $endpoint API endpoint
     * @param  string  $token    Bearer token
     * @param  array   $query    Query params (GET)
     * @param  array   $data     Body payload (POST)
     */
    public function request(
        string $method,
        string $endpoint,
        array $query = [],
        array $data = []
    ): Response {
        $http = Http::withToken($this->bearerToken)
            ->acceptJson()
            ->contentType('application/json')
            ->timeout(60);

        $res = match (strtoupper($method)) {
            'GET' => $http->get($this->baseUrl . $endpoint, $query),
            'POST' => $http->post($this->baseUrl . $endpoint, $data),
            default => throw new \InvalidArgumentException('Unsupported HTTP method'),
        };

        return $res;
    }


    public function queueRequest(
        string $method,
        string $endpoint,
        array $query = [],
        array $data = [],
        Object $institutionData
    ) {
        try {
            $userData = Auth::user();
            $uuid = Str::uuid()->toString();
            $res = null;
            $type = null;
            $payload = [
                'http_method' => $method,
                'trnsaction_type' => $endpoint,
                'reference_no' => $uuid,
                'payload' => $data,
                'response' => null,
                'order_id' => null,
                'invoice_id' => null,
                'product_id' => null,
                'stock_id' => null,
                'stock_history_id' => null,
                'status' => '',
                'is_sent' => false,
                'uri' => $endpoint,
                'institution_id' => $institutionData->id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ];

            if (!empty($institutionData)) {
                if ($institutionData->call_efris_direct) {
                    $response = $this->request($method, $endpoint, $query, $data);
                    $res = $response->json();
                    $payload['response'] = $res;
                    $payload['is_sent'] = true;
                    $payload['status'] = "Active";
                    $payload['success'] = $res->status->success;
                    $payload['return_code'] = $res->status->returnCode;
                    $payload['return_message'] = $res->status->returnMessage;
                    $type = "Direct";
                    // log::info("**************** EFRIS RESPONSE => Creating receipt ****************");
                    // log::info($response->json());
                } else {

                        $payload['status'] = "Pending";
                        $type = "Queued";
                        // log::info("**************** EFRIS RESPONSE => Queued for processing ****************");
                        // log::info($payload);

                }
            }
            $this->createEfrisOutBoundRecord($payload);
            // $payload = [
            //     'http_method' => strtoupper($method),
            //     'uri' => $endpoint,
            //     'payload' => json_encode($data),
            //     'is_sent' => false,
            // ];

            return ['res' => $res, 'type'=> $type];

            // $this->createEfrisOutBoundRecord($payload);
        } catch (\Throwable $th) {
            throw $th;
        }
    }



    // public function createEfrisOutBound($data): EfrisOutBound
    // {
    //     try {
    //         $efrisOutBout = EfrisOutBound::create($data);
    //         return $efrisOutBout;
    //     } catch (\Throwable $th) {
    //         throw $th;
    //     }
    // }


    // public function consumeEfrisPenningQueue()
    // {
    //     // code to consume pending efris outbound queue
    //     $efrisOutBout = EfrisOutBound::where('is_sent', false)->limit(20)->get();
    //     foreach ($efrisOutBout as $outBound) {
    //         // Process each outbound record
    //        $outBound = $this->request(
    //             $outBound['http_method'],
    //             $outBound['uri'],
    //             [],
    //             $outBound['payload'],
    //         );

    //         $outBound = $outBound->json();
    //     }
    // }

    public function createEfrisOutBoundRecord($data): ?EfrisOutBound
    {
        try {
            $userData = Auth::user();
            $uuid = Str::uuid()->toString();

            // $data = [
            //     'trnsaction_type' => $uri,
            //     'reference_no' => $uuid,
            //     'payload' => $payload,
            //     'response' => $response,
            //     'order_id' => null,
            //     'invoice_id' => null,
            //     'product_id' => null,
            //     'stock_id' => null,
            //     'stock_history_id' => null,
            //     'status' => $status,
            //     'is_sent' => true,
            //     'uri' => $uri,
            //     'http_method' => $httpMethod,
            //     'institution_id' => $userData->institution_id,
            //     'branch_id' => $userData->branch_id,
            //     'created_by' => $userData->id,
            //     'created_on' => now(),
            // ];

            $outBound = EfrisOutBound::create(
                // ['id' => $id],
                $data
            );

            return $outBound;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
