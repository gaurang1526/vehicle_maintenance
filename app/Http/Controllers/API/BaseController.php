<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller as Controller;

class BaseController extends Controller
{
    /**
<<<<<<< HEAD
     * response method. (created date: 22-03-2019, created by: Tridhya Tech)
=======
     * response method. (created date: 03-06-2019, created by: Tridhya Tech)
>>>>>>> 090baccf774927eceaa142b8189eebeede343c10
     *
     * @param [bool] success
     * @param [int] status_code
     * @param [string] message
     * @param [object] data
     * @param [int] http_code
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($success, $status_code, $message, $data= [], $http_code = 200)
    {

        $response = [
            'success' => $success,
            'status_code' => $status_code,
            'message' => $message,
            'data'    => $data,

            'data'    => $data,
            'success' => $success,
            'message' => $message,

        ];
        
        return response()->json($response, $http_code);
    }

    public function generateVerificationCode() {
        $digits = 4;
        return rand(pow(10, $digits - 1), pow(10, $digits) - 1);
    }
}