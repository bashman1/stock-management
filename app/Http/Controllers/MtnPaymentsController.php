<?php

namespace App\Http\Controllers;

use App\Models\MtnPayments;
use Illuminate\Http\Request;

class MtnPaymentsController extends Controller
{
    public function getMOMOAuth(){
        $uuid=$this->generateUuid();
        $subscriptionSecKey = env("SUBSCRIPTION_SEC_KEY");
        $postData = ["uuid"=>$uuid, "key"=>$subscriptionSecKey];
        $createApiUser=$this->generateAPIUser($postData);
        if($createApiUser != 201){
            return $this->genericResponse(false, "Failed to create api user", 500, $createApiUser);
        }
        $getApiUser=$this->getAPIUser($postData);
        if($getApiUser != 200){
            return $this->genericResponse(false, "Failed to get api user", 500, $getApiUser);
        }
        $apiKey = $this->generateAPIKey($postData);

        return $this->genericResponse(true, "Testing the end point", 200, $apiKey);
    }



    /**
     * Creating MOMO open API user
     * @param postData
     * @return http_code
     * @author Bashir
     */
    public function generateAPIUser($postData){
        $url = "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser";
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        # Request headers
        $headers = array(
            'Content-Type: application/json',
            'Cache-Control: no-cache',
            'Ocp-Apim-Subscription-Key:'.$postData['key'],
            'X-Reference-Id:'.$postData['uuid'],);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        # Request body
        $request_body = '{
            providerCallbackHost: "https://webhook.site/57076fb5-0d0d-4ad6-ac58-d4de34a98040"
        }';
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);

        $resp = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $http_code;
    }


    /**
     * Check if the API user was created successfully
     * @param postData
     * @return http_code
     * @author Bashir
     */
    public function getAPIUser($postData){
        $url = "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/".$postData['uuid'];
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        # Request headers
        $headers = array(
            'Content-Type: application/json',
            'Cache-Control: no-cache',
            'Ocp-Apim-Subscription-Key:'.$postData['key'],);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $resp = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $http_code;
    }


    /**
     * 
     */
    public function generateAPIKey($postData){
        // $url = "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/".$postData['uuid']."/apikey";
        // $curl = curl_init($url);

        // curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        // curl_setopt($curl, CURLOPT_URL, $url);
        // curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // # Request headers
        // $headers = array(
        //     'Content-Type: application/json',
        //     'Cache-Control: no-cache',
        //     'Ocp-Apim-Subscription-Key:'.$postData['key']);
        // curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        // $resp = curl_exec($curl);
        // $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        // curl_close($curl);
        // return ["httpCode"=>$http_code, "response"=>$url ];

        $url = "https://sandbox.momodeveloper.mtn.com/v1_0/apiuser/".$postData['uuid']."/apikey";
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        # Request headers
        $headers = array(
            'Cache-Control: no-cache',
            'Content-Type: application/json',
            'Ocp-Apim-Subscription-Key:'.$postData['key'],
            'Content-Length: ' . strlen('{}'));
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $request_body = '{}';
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);

        $resp = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        // var_dump($resp);
        return ["httpCode"=>$http_code, "response"=>$resp , "url"=>$url];
    }




}


