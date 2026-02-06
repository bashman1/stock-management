<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Models\EfrisOutBound;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EfrisApiService
{
    protected string $baseUrl;
    protected string $bearerToken;

    public function __construct()
    {
        $this->baseUrl = env('EFRIS_BASE_URL');
        $this->bearerToken = env('EFRIS_BEARER_TOKEN');
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

        $this->createEfrisOutBoundRecord(
            $endpoint,
            $data,
            $res->json(),
            strtoupper($method)
        );

        return $res;
    }



    public function createEfrisOutBound($data)
    {
        try {
            $efrisOutBout = EfrisOutBound::create($data);
            return $efrisOutBout;
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function consumeEfrisPenningQueue()
    {
        // code to consume pending efris outbound queue
        $efrisOutBout = EfrisOutBound::where('is_sent', false)->limit(20)->get();
        foreach ($efrisOutBout as $outBound) {
            // Process each outbound record
           $outBound = $this->request(
                $outBound['http_method'],
                $outBound['uri'],
                [],
                $outBound['payload'],
            );

            $outBound = $outBound->json();
        }
    }

    public function createEfrisOutBoundRecord($uri, $payload, $response, $httpMethod){
        try {
            $userData = Auth::user();
            $uuid = Str::uuid()->toString();
            $outBound = EfrisOutBound::create([
                'trnsaction_type' => $uri,
                'reference_no' => $uuid,
                'payload' => $payload,
                'response' => $response,
                'order_id' => null,
                'invoice_id' => null,
                'product_id' => null,
                'stock_id' => null,
                'stock_history_id' => null,
                'status' => 'Active',
                'is_sent' => true,
                'uri' => $uri,
                'http_method' => $httpMethod,
                'institution_id' => $userData->institution_id,
                'branch_id' => $userData->branch_id,
                'created_by' => $userData->id,
                'created_on' => now(),
            ]);
            return $outBound;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
