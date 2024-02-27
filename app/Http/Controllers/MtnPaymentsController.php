<?php

namespace App\Http\Controllers;

use App\Models\MtnPayments;
use Illuminate\Http\Request;

class MtnPaymentsController extends Controller
{

    public function getMOMOAuth(){
        $url = "https://sandbox.momodeveloper.mtn.com/collection/v1_0/bc-authorize";
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        # Request headers
        $headers = array(
            'Content-Type: application/x-www-form-urlencoded',
            'Cache-Control: no-cache',);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        # Request body
        $request_body = 'login_hint=ID:{msisdn}/MSISDN&scope={scope}&access_type={online/offline}';
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_body);

        $resp = curl_exec($curl);
        curl_close($curl);
        // var_dump($resp);

        return $this->genericResponse(true, "Testing the end point", 200, $resp);
    }
}
