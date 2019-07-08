<?php

namespace App\Http\Controllers\API;



use Illuminate\Http\Request;

use App\Http\Controllers\API\BaseController as BaseController;

use Auth;

use Validator;

use App\Repairer;

use App\RepairerBrandAssign;

use App\RepairerServiceAssign;

use App\RepairerAddress;

use App\RepairerTeam;

use App\BookingRepairerStatus;

use App\BookService;

use App\Booking;

use App\VehicleBrand;

use App\Service;

use App\VehicleTypeServiceAssign;

use App\VehicleBrandMultipleServiceAssign;

use App\RepairerWorkingDay;

use Carbon\Carbon;

use Lcobucci\JWT\Parser;

use Exception;

use Illuminate\Support\Facades\DB;


class RepairerApiController extends BaseController

{

    public function __construct() {

        $this->Repairer = new Repairer();

        $this->RepairerAddress = new RepairerAddress();

        $this->RepairerServiceAssign = new RepairerServiceAssign();

        $this->RepairerBrandAssign = new RepairerBrandAssign();

        $this->RepairerTeam = new RepairerTeam();

        $this->VehicleBrand = new VehicleBrand();

        $this->Service = new Service();

        $this->RepairerWorkingDay = new RepairerWorkingDay();

        $this->BookingRepairerStatus = new BookingRepairerStatus();

        $this->BookService = new BookService();

        $this->Booking = new Booking();

    }



    //Login Api 06-06-2019

    public function repairerLogin(Request $request){



        $validator = Validator::make($request->all(), [

            'phoneNumber' => 'required|regex:/[0-9]{10}/',

        ]);



        if($validator->fails()) {

            $status_code = config('response_status_code.invalid_input');

            return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

        }



        $repairer =  $this->Repairer->getRepairerByPhoneNumber($request['phoneNumber']);





        if(!$repairer) {

            $status_code = config('response_status_code.user_not_registered');

            return $this->sendResponse(false, $status_code, trans('message.user_not_registered'));

        }



        $isActive = ($repairer->is_active == '1') ? true : false;

        $isIMEIVerified = (empty($repairer->imei_number) || $repairer->imei_number == $request['IMEI']) ? true : false;



        $response_repairer['isActive'] = $isActive;

        $response_repairer['isIMEIVerified'] = $isIMEIVerified;



        if(!$isActive) {

            $status_code = config('response_status_code.user_not_active');

            return $this->sendResponse(true, $status_code, trans('message.user_not_active'), $response_repairer);

        }

       /* if(!$isIMEIVerified) {

            $status_code = config('response_status_code.imei_number_mismatch');

            return $this->sendResponse(true, $status_code, trans('message.imei_number_mismatch'), $response_repairer);

        }*/



        $verificationCode = $this->generateVerificationCode();

        $verificationCode = 1234;



        $repairer->verification_code = $verificationCode;

        $repairer->save();



        $status_code = config('response_status_code.otp_sent_success');

        return $this->sendResponse(true, $status_code, trans('message.otp_sent_success'), $response_repairer);

    }

    public function logout(Request $request) {
        $userId = $request->user()->id;
        $user = Repairer::where('id', $userId)->first();
        if ($user) {
            $user->device_token = "";
            $user->device_type = "";
            //$user->last_login = $user->current_login;
            $user->save();
            // remove token
            $value = $request->bearerToken();
            $id = (new Parser())->parse($value)->getHeader('jti');
            
            DB::table('oauth_access_tokens')
                ->where('id', $id)
                ->update([
                    'revoked' => true
            ]);
            $status_code = config('response_status_code.user_logged_out');
            return $this->sendResponse(true, $status_code, trans('message.user_logged_out'));
        } else {
            $status_code = config('response_status_code.user_not_registered');
            return $this->sendResponse(false, $status_code, trans('message.user_not_registered'));
        }
    }

    //Verify Otp Api 06-06-2019

    public function repairerLoginVerifyOTP(Request $request){

        $validator = Validator::make($request->all(), [

            'phoneNumber' => 'required|regex:/[0-9]{10}/',

            'otp' => 'required',

            'deviceToken' => 'required',

            'deviceType' => 'required',

        ]);



        if($validator->fails()) {

            $status_code = config('response_status_code.invalid_input');

            return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

        }



        if (isset($request['deviceType']) && $request['deviceType'] !== 'A' && $request['deviceType'] !== 'I') {

            $status_code = config('response_status_code.invalid_device_type');

            return $this->sendResponse(false, $status_code, trans('message.invalid_device_type'));

        }



        $repairer = $this->Repairer->getRepairerByPhoneNumber($request['phoneNumber']);



        if(!$repairer) {

            $status_code = config('response_status_code.invalid_phone');

            return $this->sendResponse(false, $status_code, trans('message.invalid_phone'));

        }



        $repairer = Repairer::checkRepairerOtp($request['phoneNumber'],$request['otp']);

      



        if(!$repairer) {

            $status_code = config('response_status_code.invalid_otp');

            return $this->sendResponse(false, $status_code,trans('message.invalid_otp'));

        }



        $isActive = ($repairer->is_active == '1') ? true : false;

        $isIMEIVerified = (empty($repairer->imei_number) || $repairer->imei_number == $request['IMEI']) ? true : false;



        $response_repairer['isActive'] = $isActive;

        $response_repairer['isIMEIVerified'] = $isIMEIVerified;



        if(!$isActive) {

            $status_code = config('response_status_code.user_not_active');

            return $this->sendResponse(true, $status_code, trans('message.user_not_active'), $response_repairer);

        }



        /*if(!$isIMEIVerified) {

            $status_code = config('response_status_code.imei_number_mismatch');

            return $this->sendResponse(true, $status_code, trans('message.imei_number_mismatch'), $response_repairer);

        }*/

        if(isset($repairer->device_token) && $repairer->device_token != $request['deviceToken']){

            //$notifyLogoutEvent = $this->send_push_notification($customer->device_token, 'logout');

        }



        $repairer->imei_number = $request['IMEI'];

        $repairer->device_token = $request['deviceToken'];

        $repairer->device_type = $request['deviceType'];

        $repairer->is_active = 1;

        $repairer->save();

        

        //Response Data starts

            $response_repairer['userId'] = $repairer->id;

            $response_repairer['email'] = $repairer->email;

            $response_repairer['firstName'] = $repairer->first_name;

            $response_repairer['lastName'] = $repairer->last_name;

            $response_repairer['imageUrl'] = str_replace("\\", "/", $repairer->photo);

            $response_repairer['phoneNumber'] = $repairer->phone_number;

            $response_repairer['isActive'] = $isActive;

            $response_repairer['token'] = $repairer->createToken('MyTrafficViolation')->accessToken;

            $response_repairer['middleName'] = NULL;

            $response_repairer['panNumber'] = NULL;

            $response_repairer['drivingLicence'] = NULL;

            $response_repairer['AadharNumber'] = NULL;

            $response_repairer['secondaryPhoneNumber'] = NULL;

            $response_repairer['address'] = $repairer->address;

            $response_repairer['bloodGroup'] = NULL;

            $response_repairer['disease'] = NULL;

            $response_repairer['allergy'] = NULL;

            $response_repairer['pfAccountNumber'] = NULL;

            $response_repairer['bankAccount'] = NULL;

            $response_repairer['bankName'] = NULL;

            $response_repairer['bankBranch'] = NULL;

            $response_repairer['bankIFSC'] = NULL;

            $response_repairer['IMEI'] = $repairer->imei_number;

            $response_repairer['joiningDate'] = NULL;

            $response_repairer['targetEventCount'] = NULL;

            $response_repairer['dataDeleteTime'] =  Null;

            $response_repairer['recordingQuotaMinutes'] = NULL;

            if($repairer->is_repairer == 1){

                $response_repairer['user_type'] = 'Repairer';

            }else{

                $response_repairer['user_type'] = 'Customer';

            }

            

        //Response Data Ends



        $status_code = config('response_status_code.user_login_success');

        return $this->sendResponse(true, $status_code, trans('message.user_login_success'), $response_repairer);

    }





    public function getUserType(Request $request){

        $userId = $request->user()->id;

        $response_repairer = array();

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }

        try{

            $repairer = Repairer::where('id', $userId)->first();



            if($repairer->is_repairer == 1){

                $response_repairer['user_type'] = 'Repairer';

            }else{

                $response_repairer['user_type'] = 'Customer';

            }



            $isActive = ($repairer->is_active == '1') ? true : false;

            $response_repairer['isActive'] = $isActive;

            $response_repairer['isIMEIVerified'] = true ;



            $response_repairer['userId'] = $repairer->id;

            $response_repairer['email'] = $repairer->email;

            $response_repairer['firstName'] = $repairer->first_name;

            $response_repairer['lastName'] = $repairer->last_name;

            $response_repairer['imageUrl'] = $repairer->photo;

            $response_repairer['phoneNumber'] = $repairer->phone_number;

            $response_repairer['isActive'] = $repairer->is_active;

           // $response_repairer['token'] = $repairer->createToken('MyTrafficViolation')->accessToken;

            $response_repairer['middleName'] = NULL;

            $response_repairer['panNumber'] = NULL;

            $response_repairer['drivingLicence'] = NULL;

            $response_repairer['AadharNumber'] = NULL;

            $response_repairer['secondaryPhoneNumber'] = NULL;

            $response_repairer['address'] = $repairer->address;

            $response_repairer['bloodGroup'] = NULL;

            $response_repairer['disease'] = NULL;

            $response_repairer['allergy'] = NULL;

            $response_repairer['pfAccountNumber'] = NULL;

            $response_repairer['bankAccount'] = NULL;

            $response_repairer['bankName'] = NULL;

            $response_repairer['bankBranch'] = NULL;

            $response_repairer['bankIFSC'] = NULL;

            $response_repairer['IMEI'] = $repairer->imei_number;

            $response_repairer['joiningDate'] = NULL;

            $response_repairer['targetEventCount'] = NULL;

            $response_repairer['dataDeleteTime'] =  Null;

            $response_repairer['recordingQuotaMinutes'] = NULL;



            $status_code = config('response_status_code.fetch_user_type');

            return $this->sendResponse(true, $status_code, trans('message.fetch_user_type'), $response_repairer);



        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $response_repairer);

        }

    }



    //Get Repairer Profile Api 06-06-2019

    public function getRepairerProfile(Request $request){

        $data = array();

        $response_repairer = array();



        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }



        try{

            $validator = Validator::make($request->all(), [

                'languageCode' => 'required',

                'userId' => 'required',

            ]);



            if($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code,trans('message.invalid_input'));

            }



            $userId = $request->userId;

            $repairer = Repairer::find($userId);



            if(empty($repairer)) {

                $status_code = config('response_status_code.repairer_not_found');

                return $this->sendResponse(true, $status_code, trans('message.repairer_not_found'));

            }



            $repairerAddress =  $this->RepairerAddress->getRepairerAddress($userId);

            $repairerAddress = (empty($repairerAddress)) ? NULL : $repairerAddress;

            

            $brands =  $this->RepairerBrandAssign->getRepairerBrands($userId);

            $brands = (empty($brands)) ? NULL : $brands;

            $servicesMain = array();

           

            if(!empty($brands)){

                foreach ($brands as $key => $value) {

                   $servicesMain[$key]['brandId'] = $value['id'];

                   $servicesMain[$key]['brandName'] = $value['name'];

                   $services =  $this->RepairerServiceAssign->getRepairerServices($value['repairerBrandAssignsId'],$userId);

                   $servicesMain[$key]['services'] = $services;

                }

            }

 

            $workingDays =  $this->RepairerWorkingDay->getRepairerWorkingDays($userId);

            $workingDays = (empty($workingDays)) ? NULL : $workingDays;



            //Respone Data starts

                $repairer->garage_photo = str_replace("\\\\", "/", $repairer->garage_photo);

                $repairer->photo = str_replace("\\", "/", $repairer->photo);

                $repairer->document_path = str_replace("\\", "/", $repairer->document_path);



                $response_repairer['garagePhoto'] = json_decode($repairer->garage_photo);

                $response_repairer['repairerImage'] = $repairer->photo;

                $response_repairer['document'] = $repairer->document_path;

                $response_repairer['garageName'] = $repairer->garageName;

                $response_repairer['speciallity'] = $repairer->speciality;

                $response_repairer['rating'] = NULL;

                $response_repairer['address'] = $repairerAddress;

                $response_repairer['contactNumber'] = $repairer->phone_number;

                $response_repairer['about'] = $repairer->about;

                $response_repairer['brands_services'] = $servicesMain;

                $response_repairer['workingDays'] = $workingDays;

                $response_repairer['start_time'] = $repairer->start_time;

                $response_repairer['end_time'] = $repairer->end_time;

                $response_repairer['first_name'] = $repairer->first_name;

                $response_repairer['last_name'] = $repairer->last_name;

                $response_repairer['email'] = $repairer->email;

                $response_repairer['isPickup'] = $repairer->is_pickup;

                $response_repairer['isAuthorize'] = $repairer->is_authorized;

                $response_repairer['isTopDealer'] = $repairer->is_topdealer;

                $response_repairer['VehicleId'] = $brands[0]->VehicleId;

                $response_repairer['vehicleName'] = $brands[0]->vehicleName;



            //Response Data Ends

            $status_code = config('response_status_code.data_fetched_success');

            return $this->sendResponse(true, $status_code, trans('message.data_fetched_success'), $response_repairer);

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }

    }



    //Get Team Member Api 06-06-2019

    public function getTeamMembers(Request $request){

        $userId = $request->user()->id;

        $data = array();

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }



        try{

            $validator = Validator::make($request->all(), [

                'languageCode' => 'required',

            ]);



            if($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

            }



            $repairerTeam = $this->RepairerTeam->getRepairerTeam($userId);

            if($repairerTeam->isEmpty()) {

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code, trans('message.exception_code'));

            }

            if(count($repairerTeam)>0){

                foreach ($repairerTeam as $key => $value) {

                    $repairerTeam[$key]['photo'] = str_replace("\\", "/", $value['photo']);

                    $repairerTeam[$key]['document'] = str_replace("\\", "/", $value['document']);

                }

            }

            $status_code = config('response_status_code.data_fetched_success');

            return $this->sendResponse(true, $status_code, trans('message.data_fetched_success'), $repairerTeam);

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }

    }



    // Add Edit Team Memeber Api 06-06-2019

    public function addTeamMember(Request $request){
       
        $userId = $request->user()->id;
        $data = array();
        if(!Auth::guard('api')->check()) {
            $status_code = config('response_status_code.unauthorized_request');
            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));
        }
        try{
            $validator = Validator::make($request->all(), [
                'languageCode' => 'required',
                'memberId' => 'required',
                'memberName' => 'required',
                'memberImage' => 'required|image',
                'memberContact' => 'required',
                'memberLicenceNumber' => 'required',
                'memberLicenceImage' => 'required|image',
            ]);

            if($validator->fails()) {
                $status_code = config('response_status_code.invalid_input');
                return $this->sendResponse(false, $status_code,trans('message.invalid_input'));
            }

            $repairerTeam = $this->RepairerTeam->addTeamMember($request,$userId);
            if(empty($repairerTeam)){
                $status_code = config('response_status_code.exception_code');
                return $this->sendResponse(true, $status_code,trans('message.exception_code'));
            }else{
                $status_code = config('response_status_code.data_save_successfully');
                return $this->sendResponse(true, $status_code, trans('message.data_save_successfully'),$repairerTeam);
            }
        }catch (Exception $e) {
            $status_code = config('response_status_code.exception_code');
            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);
        }
    }  



    // Add Repairer Profile Data 17-06-2019

    public function addRepairerProfileData(Request $request){

       $userId = $request->user()->id;

        $data = array();

        $response_repairer = array();

        

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }



        try{

            // Validation Rule Check Starts

                $validator = Validator::make($request->all(), [

                    'languageCode' => 'required',

                    'address' => 'required',

                    'latitude' => 'required',

                    'longitude' => 'required',

                    'garageName' => 'required',

                    'speciality' => 'required',

                    'about' => 'required',

                    'isTopDealer' => 'required|boolean',

                    'isPickup' => 'required|boolean',

                    'isAuthorize' => 'required|boolean',

                    'isActive' => 'required|boolean',

                    'vehicleId' => 'required',

                   // 'brands' => 'array|required',

                    //'services' => 'array',

                    'workingDays' => 'array',

                    "phoneNumber" =>"required",

                    "email" =>"required",

                    "name" =>"required",

                ]);

            // Validation Rule Check Ends

            

            if($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

            }

          



            $repairer = Repairer::find($userId);

           

            if(empty($repairer)) {

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,trans('message.exception_code'));

            }



            //Repairer Address Starts

                $repairerAddress = RepairerAddress::where("repairer_id",$userId)->first();

                if(empty($repairerAddress)){

                    $repairerAddress = new RepairerAddress();

                }

                $repairerAddress = $this->RepairerAddress->saveRepairerAddress($userId,$repairerAddress,$request);

            //Repairer Address Ends

            

            //Repairer Starts  

                $repairer = $this->Repairer->saveRepairer($repairer,$request);

            //Repairer Ends

            

            //Repairer Brand Assign Starts

                $repairerTypeBrandAssign = $this->RepairerBrandAssign->saveRepairerBrandServices($userId,$request);

            //Repairer Brand Assign Ends



            //Repairer Working Days Starts

                $repairerWorkingDay = $this->RepairerWorkingDay->saveRepairerWorkingDays($userId,$request);

            //Repairer Working Days Ends



            if($repairerAddress == 1 && $repairer == 1 && $repairerTypeBrandAssign == 1 && $repairerWorkingDay == 1){

                $status_code = config('response_status_code.data_save_successfully');

                return $this->sendResponse(true, $status_code, trans('message.data_save_successfully'),$repairerAddress);

            }else{

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,trans('message.exception_code'));

            }

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }    

        

    }



    // Add Repairer Profile Image 17-06-2019

    public function addRepairerProfileImage(Request $request){

        $userId = $request->user()->id;
        $data = array();
        $response_repairer = array();
            

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        } 



        try{

            if($request->isEdit == "false"){
                // Validation Rule Check Starts
                $validator = Validator::make($request->all(), [
                    'repairerImage' => 'required|image',
                    'document' => 'required|file',
                    'garagePhoto' => 'required',
                ]);
                // Validation Rule Check Ends
                if($validator->fails()) {
                    $status_code = config('response_status_code.invalid_input');
                    return $this->sendResponse(false, $status_code,trans('message.invalid_input'));
                }
            }

            $repairer = Repairer::find($userId);

            if(empty($repairer)) {

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,trans('message.exception_code'));

            }



            //Repairer Starts  

                $repairerSave = $this->Repairer->saveRepairerImage($repairer,$request);

            //Repairer Ends



            //Respone Data starts
                if($request->hasFile('repairerImage')) {
                    $response_repairer['repairerImage'] = $repairer->photo;
                }
                if($request->hasFile('document')) {
                    $response_repairer['document'] = $repairer->document_path;
                }    
                if(!empty($request->file('garagePhoto'))){
                    $response_repairer['garagePhoto'] = $repairer->garage_photo;
                }    

            //Response Data Ends



            if($repairerSave == 1){

                $status_code = config('response_status_code.data_save_successfully');

                return $this->sendResponse(true, $status_code, trans('message.data_save_successfully'), $response_repairer);

            }

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }    

    }



    // Add Repairer Address 10-6-2019

    public function saveAddress(Request $request){

        $userId = $request->user()->id;

        $data = array();

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }



        try{

            $validator = Validator::make($request->all(), [

                'languageCode' => 'required',

                'addressId' => 'required|numeric',

                'address' => 'required',

                'addressType' => 'required',

                'latitude' => 'required',

                'longitude' => 'required',

            ]);



            if($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code,trans('message.invalid_input'));

            }



            $repairerAddress = ($request->addressId == 0) ? new RepairerAddress() : RepairerAddress::find($request->addressId);



            if(empty($repairerAddress)) {

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,trans('message.exception_code'));

            }



            $repairerAddressSaved = $this->RepairerAddress->saveRepairerAddress($userId,$repairerAddress,$request);



            if($repairerAddressSaved == 1){

                $status_code = config('response_status_code.data_save_successfully');

                return $this->sendResponse(true, $status_code, trans('message.data_save_successfully'),$repairerAddress);

            }

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }    

    }



    // Delete Repairer Address 10-06-2019

    public function deleteAddress(Request $request){

        $userId = $request->user()->id;

        $data = array();



        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }



        try{

            $validator = Validator::make($request->all(), [

                'addressId' => 'required|numeric',

            ]);



            if($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code,trans('message.invalid_input'));

            }

            

            $RepairerAddress = RepairerAddress::find($request->addressId);

            if(!empty($RepairerAddress)){

                $RepairerAddress->delete();

                $status_code = config('response_status_code.data_delete_successfully');

                return $this->sendResponse(true, $status_code, trans('message.data_delete_successfully'));

            }else{

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,trans('message.exception_code'), $data); 

            }

            

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }  

    }



    //Delete Repairer Team Member 10-06-2019

    public function deleteTeamMember(Request $request){

        $userId = $request->user()->id;

        $data = array();

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }



        try{

            $validator = Validator::make($request->all(), [

                'teamMemberId' => 'required|numeric',

            ]);



            if($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

            }

            

            $repairerTeam = RepairerTeam::find($request->teamMemberId);

            if(!empty($repairerTeam)){

                $repairerTeam->delete();

                $status_code = config('response_status_code.data_delete_successfully');

                return $this->sendResponse(true, $status_code, trans('message.data_delete_successfully'));

            }else{

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,trans('message.exception_code'), $data); 

            }



           

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }

    }



    //Get Repairer Booking List 24-06-2019

    public function getRepairerBookingList(Request $request){

        $userId = $request->user()->id;

        $response_data = array();

        $data = array();

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }



        try{

            $validator = Validator::make($request->all(), [

                'languageCode' => 'required',

                'bookingListType' => 'required',

            ]);



            if($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

            }

            $bookingDetails = $this->BookingRepairerStatus->getRepairerBookingListDetails($request->bookingListType,$userId);


            if($bookingDetails->isEmpty()) {

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,trans('message.exception_code'));

            }

            foreach ($bookingDetails as $key => $value) {

                $bookingServices = $this->BookService->getBookingService($value->BookingId);

                $bookingServices = ($bookingServices->isEmpty()) ? Null : $bookingServices;

                 

                $response_data[$key]['bookingId'] = $value->BookingId;

                $response_data[$key]['customerName'] = $value->first_name;

                $response_data[$key]['customerImage'] = str_replace("\\", "/",$value->photo);

                $response_data[$key]['serviceRating'] = Null;

                $response_data[$key]['location'] = $value->address;

                $response_data[$key]['cost'] = Null;

                $response_data[$key]['status'] = $value->status;

                $response_data[$key]['type'] = $value->type;

                $response_data[$key]['dateAndTime'] = $value->created_at;

                $response_data[$key]['vehicleName'] = $value->makeName;

                $response_data[$key]['vehicleBrand'] = $value->brandName;

                $response_data[$key]['isReviewed'] = Null;

                $response_data[$key]['services'] = $bookingServices;

            }


            $status_code = config('response_status_code.data_fetched_success');

            return $this->sendResponse(true, $status_code, trans('message.data_fetched_success'), $response_data);



        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }    

    }



    //Get Last Booking Details 24-06-2019

    public function changeBookingStatus(Request $request){

        $userId = $request->user()->id;
        $data = array();

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }



        try{

            if($request->status == 4){

                $validator = Validator::make($request->all(), [

                    'languageCode' => 'required',

                    'bookingId' => 'required',

                    'status' => 'required',

                    'pickupboyId' => 'required',

                ]);

            }else if($request->status == 7){

                $validator = Validator::make($request->all(), [

                    'languageCode' => 'required',

                    'bookingId' => 'required',

                    'status' => 'required',

                    'deliveryBoyId' => 'required',

                    'amount' => 'required',

                ]);

            }else{

                $validator = Validator::make($request->all(), [

                    'languageCode' => 'required',

                    'bookingId' => 'required',

                    'status' => 'required',

                    'statusTime' => 'required',

                ]);

            }

                

            if($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code,trans('message.invalid_input'));

            }



            $bookingRepairerStatus = $this->BookingRepairerStatus->updateBookingStatus($request,$userId);



            if($bookingRepairerStatus == 1){

                $status_code = config('response_status_code.data_save_successfully');

                return $this->sendResponse(true, $status_code, trans('message.data_save_successfully'));

            }

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }    

    }





    //Get Lat Booking Details

    public function getLastBookingDetail(Request $request){

        $userId = $request->user()->id;

        $data = array();

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }



        try{

            $validator = Validator::make($request->all(), [

                'languageCode' => 'required',

            ]);

            

            if($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code,trans('message.invalid_input'));

            }



            

            $bookingRepairerStatus = $this->BookingRepairerStatus->getLastBookingDetails($userId);               

            if(empty($bookingRepairerStatus)) {

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,trans('message.exception_code'));

            }



            $status_code = config('response_status_code.data_fetched_success');

            return $this->sendResponse(true, $status_code, trans('message.data_fetched_success'), $bookingRepairerStatus);              

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }     

    }
    public function getBookingListUser(Request $request){
        $userId = $request->user()->id;
        $data = array();
        if(!Auth::guard('api')->check()) {
            $status_code = config('response_status_code.unauthorized_request');
            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));
        }
        try{
            $validator = Validator::make($request->all(), [
                'languageCode' => 'required',
                'bookingListType' => 'required'
            ]);
            
            if($validator->fails()) {
                $status_code = config('response_status_code.invalid_input');
                return $this->sendResponse(false, $status_code,trans('message.invalid_input'));
            }

            $data = $this->Booking->getBookingListUser($userId,$request->bookingListType);
            if(count($data)){
                foreach ($data as $key => $value) {
                    $data[$key]['repairerImage'] = str_replace("\\", "/", $value['repairerImage']);
                }
            }
            $status_code = config('response_status_code.data_fetched_success');
            return $this->sendResponse(true, $status_code, trans('message.data_fetched_success'), $data);
        }catch (Exception $e) {
            $status_code = config('response_status_code.exception_code');
            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);
        } 
    }

}

        



