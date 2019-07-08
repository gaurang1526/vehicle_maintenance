<?php
namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\DB;

class AuthController extends BaseController
{

    /**
     * Login user using OTP.
     *
     * @param  Request  $request
     * @return [json] user object
     */
    public function login(Request $request) {


        // $validator = Validator::make($request->all(), [
        //     'phoneNumber' => 'required|regex:/[0-9]{10}/',
        //     'IMEI' => 'required',
        // ]);
        echo "<pre>";var_dump($request->phone_number);exit;
        if($validator->fails()) {
            $status_code = config('response_status_code.invalid_input');
            return $this->sendResponse(false, $status_code, trans('message.invalid_input'));
        }

        $user = User::where('phone_number', $request['phoneNumber'])->first();

        if(!$user) {
            $status_code = config('response_status_code.user_not_registered');
            return $this->sendResponse(false, $status_code, trans('message.user_not_registered'));
        }

        $roleCheck = User::where('phone_number', $request['phoneNumber'])->where('role_id', 2)->first();

        if(!$roleCheck) {
            $status_code = config('response_status_code.invalid_input');
            return $this->sendResponse(false, $status_code, trans('message.invalid_input'));
        }

        $isActive = ($user->is_active == '1') ? true : false;
        $isIMEIVerified = (empty($user->imei_number) || $user->imei_number == $request['IMEI']) ? true : false;

        $city = new City;
        $cityData = $city->getCityData($user->city_id);
        $cityId = $cityData->id;
        $cityName = $cityData->name;

        $response_user['isActive'] = $isActive;
        $response_user['isIMEIVerified'] = $isIMEIVerified;
        $response_user['cityId'] = $cityId;
        $response_user['cityName'] = $cityName;

        if(!$isActive) {
            $status_code = config('response_status_code.user_not_active');
            return $this->sendResponse(true, $status_code, trans('message.user_not_active'), $response_user);
        }

        if(!$isIMEIVerified) {
            $status_code = config('response_status_code.imei_number_mismatch');
            return $this->sendResponse(true, $status_code, trans('message.imei_number_mismatch'), $response_user);
        }

        if (isset($request['cityName'])) {
            if (strtolower($cityName) != strtolower($request['cityName'])) {
                $status_code = config('response_status_code.invalid_city');
                return $this->sendResponse(true, $status_code, trans('message.invalid_city'), $response_user);
            }
        }

        $verificationCode = $this->generateVerificationCode();
        $verificationCode = 1234;

        // Your Account SID and Auth Token from twilio.com/console
        $account_sid = 'AC6e7e5be571eb2f8496e062bc2144a8b1';
        $auth_token = '8a612faeeac86cb31172a1ffa9b297a4';

        // A Twilio number you own with SMS capabilities
        $twilio_number = "+14074797182";

        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            // Where to send a text message (your cell phone?)
            '+919408786647',
            array(
                'from' => $twilio_number,
                'body' => 'Your GTS verification OTP is '.$verificationCode,
            )
        );

        $user->verification_code = $verificationCode;
        $user->save();

        $status_code = config('response_status_code.otp_sent_success');
        return $this->sendResponse(true, $status_code, trans('message.otp_sent_success'), $response_user);
    }

   
}
