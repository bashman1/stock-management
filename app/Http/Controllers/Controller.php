<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

     

        /**
     * For returning responses of the application 25 oct 2022
     *
     * @param [type] $status
     * @param [type] $message
     * @param [type] $data
     * @return void
     * @author Bashir <wamulabash1@gmail.com.com>
     */
    public function genericResponse($status = true, $message, $code = 200, $data = []){
        return response()->json([
            "status"=>$status,
            "code"=>$code,
            "message"=>$message,
            "data"=>$data
        ]);
    }
}
