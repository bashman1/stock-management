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


    /**
     * get current logged in user data  04 Oct 2023
     * @return
     * @author Bashir <wamulabash1@gmail.com>
     */
    public function getUserLogin(){
        return auth()->user();
    }


    /**
     * Generating the member number staring letters
     *
     * @param [type] $input
     * @return void
     * @author Bashir <wamulabash1@gmail.com>
     */
    public function getLetters($input) {
        // Split the input string into words
        $words = explode(' ', $input);

        // Initialize an empty result string
        $result = '';

        // Loop through the words
        foreach ($words as $word) {
            // Get the first two letters of a single word or the first letter of each word (maximum 2 letters)
            $result .= strtoupper(substr($word, 0, min(1, strlen($word))));
        }

        return  substr($result, 0, 2);
    }
}
