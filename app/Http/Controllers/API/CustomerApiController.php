<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Http\Controllers\API\BaseController as BaseController;

use Auth;

use Validator;

use App\VehicleType;

use App\VehicleTypeBrandAssign;

use App\Service;

use App\Customer;

use App\VehicleMake;

use App\Repairer;

use App\Booking;

use App\City;

use App\BookService;

use App\ReviewType;

use App\VehicleBrand;

use App\Review;

use App\VehicleTypeBrandMakeAssign;

use App\RepairerAddress;

use App\BookingRepairerStatus;

use App\VehicleBrandMultipleServiceAssign;

use App\VehicleTypeServiceAssign;

use Carbon\Carbon;

use Illuminate\Support\Facades\DB;

use Exception;



class CustomerApiController extends BaseController{





    public function __construct() {

            $this->VehicleType = new VehicleType();

            $this->VehicleBrand = new VehicleBrand();

            $this->VehicleMake = new VehicleMake();

            $this->RepairerAddress = new RepairerAddress();

            $this->ReviewType = new ReviewType();

            $this->Review = new Review();

            $this->BookingRepairerStatus = new BookingRepairerStatus();

            $this->Booking = new Booking();

            $this->VehicleTypeServiceAssign = new VehicleTypeServiceAssign();

            $this->VehicleBrandMultipleServiceAssign = new VehicleBrandMultipleServiceAssign();

            $this->BookService = new BookService();

            $this->Service = new Service();

            $this->VehicleTypeBrandMakeAssign = new VehicleTypeBrandMakeAssign();

    }



    public function getVehicleType(Request $request) {



        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }

        $data = array();

        try{

           

            $userId = $request->user()->id;

            $input = $request->all();

            $data = $this->VehicleType->getVehicleType();

            $vehicleId =  $this->VehicleType->mostUseVehicleUserWise($userId);

            if(count($data) > 0){

                foreach ($data as $key => $value) {

                     if(count($vehicleId) > 0){

                        foreach ($vehicleId as $key1 => $value1) {

                            if($value1->vehicleTypeId == $value->vehicleTypeId){

                                $data[$key]->isFrequentlyUsed = true;

                            }

                        } 

                        $data[$key]->vehicleImage = str_replace("\\", "/", $value->vehicleImage);

                    }

                }

            }

            $status_code = config('response_status_code.data_get_success');

            return $this->sendResponse(true, $status_code, trans('message.data_get_success'), $data);

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }

    }



    public function getBrandList(Request $request){

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }

        $data = array();

        try{

            $validator = Validator::make($request->all(), [

                'vehicleTypeId' => 'required',

            ]);

            if ($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

            }

            $input = $request->all();



            $data =  $this->VehicleBrand->getBrandList($input['vehicleTypeId']);

            $status_code = config('response_status_code.data_get_success');

            return $this->sendResponse(true, $status_code, trans('message.data_get_success'), $data);

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }

    }



   public function getServiceList(Request $request){



        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }

        $data = array();

        try{



            $validator = Validator::make($request->all(), [

                'vehicleTypeId' => 'required',

                'brandId' => 'required',

                'languageCode' => 'required'

            ]);

            $userId = $request->user()->id;

             if ($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

            }

            $input = $request->all();



           $data = $this->Service->getServices($input['vehicleTypeId'],$input['brandId'],$userId);

            $status_code = config('response_status_code.data_get_success');



            return $this->sendResponse(true, $status_code, trans('message.data_get_success'), $data);



         }catch (Exception $e) {



             $status_code = config('response_status_code.exception_code');



             return $this->sendResponse(true, $status_code,$e->getMessage(), $data);



        }



    }



    public function getMakeList(Request $request){

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }

        $data = array();

        try{

            $validator = Validator::make($request->all(), [

                'vehicleTypeId' => 'required',

                'brandId' => 'required',

            ]);

             if ($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

            }

            $input = $request->all();

            $data = $this->VehicleMake->getMakeList($input['vehicleTypeId'],$input['brandId']);

                    

            $status_code = config('response_status_code.data_get_success');

            return $this->sendResponse(true, $status_code, trans('message.data_get_success'), $data);

            // $data = VehicleTypeBrandMakeAssign::where('vehicle_type_id',$input['vehicleTypeId'])->where('vehicle_brand_id' => $input['brandId'])->get()->toArray();

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }

    }



    public function saveSearchRepairs(Request $request){

        $userId = $request->user()->id;

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }   

        $data = array();

        try{

            $validator = Validator::make($request->all(), [

                'isAuthorizeServiceCenter' => 'required',

                'vehicleTypeId' => 'required',

                'brandId' => 'required',

                'isSelectPickupTime' => 'required',

                'addressId' => 'required',

            ]);

            $data = array();

            if ($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

            }

            $makeId = 0;

            $pickupTime = '';

            $input = $request->all();

            if($input['isAuthorizeServiceCenter'] == '0'){

                if(isset($input['makeId']) && $input['makeId'] == 0){

                       $VehicleMake = $this->VehicleMake->saveMake($input['makeName']);

                       $VehicleTypeBrandMakeAssign = $this->VehicleTypeBrandMakeAssign->saveVehicleTypeBrandMakeAssign($input['vehicleTypeId'],$input['brandId'],$VehicleMake->id);

                       

                       $makeId = $VehicleMake->id;

                }else{

                       $makeId = $input['makeId'];

                }

            }

            $addressId = $input['addressId'];

            $booking = $this->Booking->saveBooking($userId,$makeId,$input);

            

            if(!empty($input['services'])){

                foreach ($input['services'] as $key => $value) {



                    if($value['serviceId'] == 0){

                        $Service = $this->Service->customServiceCreate($value['serviceName'],$userId);

                        

                        $serviceId =  $Service->id;



                        $VehicleTypeServiceAssign = $this->VehicleTypeServiceAssign->getVehicleTypeServiceAssign($input);

                        if(!empty($VehicleTypeServiceAssign)){

                            $VehicleBrandMultipleServiceAssign = $this->VehicleBrandMultipleServiceAssign->saveVehicleBrandMultipleServiceAssign($VehicleTypeServiceAssign['id'],$serviceId);

                        }else{

                            $VehicleTypeServiceAssign = $this->VehicleTypeServiceAssign->saveVehicleTypeServiceAssign($input);

                           



                             $VehicleBrandMultipleServiceAssign = $this->VehicleBrandMultipleServiceAssign->saveVehicleBrandMultipleServiceAssign($VehicleTypeServiceAssign['id'],$serviceId);



                        }

                    }else{

                        $serviceId =  $value['serviceId'];

                    }

                    $BookService = $this->BookService->saveBookService($booking->id,$serviceId);

                   

                }

            }  

            $bookingAddress = $this->RepairerAddress->getRepairerAddressBookingIdWise($booking->id);

           

            if(empty($bookingAddress)) {

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,trans('message.exception_code'));

            }

            $latitude =  $bookingAddress->latitude;

            $longitude =  $bookingAddress->longitude;

            $addressId =  $bookingAddress->addressId;

            $results = $this->RepairerAddress->getRepairerAddressIdWise($addressId,$latitude,$longitude);

            

    

            if(empty($results)){

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,trans('message.exception_code'));

            }

            foreach ($results as $key => $value) {

               

                $bookingRepairerStatus = $this->BookingRepairerStatus->saveMultipleBookingRepairerStatus($booking->id,$value);

                

            }



            $data['bookingId'] = $booking->id;

            $status_code = config('response_status_code.data_save_successfully');

            return $this->sendResponse(true, $status_code, trans('message.data_save_successfully'), $data);

            }catch (Exception $e) {

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

            }

    }



    public function getSaveAddressList(Request $request){

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }

        $data = array();

        try{

                

                $userId = $request->user()->id;

                

                $input = $request->all();

                $data = $this->RepairerAddress->getRepairerAddress($userId);



                $status_code = config('response_status_code.data_get_success');

                return $this->sendResponse(true, $status_code, trans('message.data_get_success'), $data);

        }catch (Exception $e) {

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }

    }



    public function getReviewTypes(Request $request){

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }

        $data = array();

        try{

                $validator = Validator::make($request->all(), [

                    'languageCode' => 'required',

                ]);

                if($validator->fails()) {

                    $status_code = config('response_status_code.invalid_input');

                    return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

                }

                $input = $request->all();

                $data['ratingList'] = $this->ReviewType->getReviewType();

                $data['minimum_rating'] = setting('site.minimum_rating');



                $status_code = config('response_status_code.data_get_success');

                return $this->sendResponse(true, $status_code, trans('message.data_get_success'), $data);

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }

    }



    public function saveReview(Request $request){

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }

        $data = array();

        try{

                $validator = Validator::make($request->all(), [

                    'languageCode' => 'required',

                    'ratingList' => 'required',

                    'userId' => "required",

                    'bookingId' => "required",

                    'feedback' => "required",

                ]);

                if($validator->fails()) {

                    $status_code = config('response_status_code.invalid_input');

                    return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

                }

                $input = $request->all();
                $reviewStore = array();
                $sum = 0;
               // echo "<pre>";print_r($input['ratingList']);exit;
                if(count($input['ratingList']) > 0){

                    foreach ($input['ratingList'] as $key => $value) {
                        $reviewStore[] = $value['rating'];
                        $sum += $value['rating'];
                        // $Review = $this->Review->saveReview($input['bookingId'],$value['id'],$value['rating'],$input['feedback']);

                    }

                }
                $count = count($reviewStore);
                $string = implode(",",$reviewStore);
                $average = $sum/$count;
                // echo "<pre>";print_r($count);echo "<pre>";print_r($sum);
                // echo "<pre>";print_r(number_format($average ,2));exit;
                $Review = $this->Review->saveReview($input['bookingId'],$string,number_format($average ,2),$input['feedback']);
                $status_code = config('response_status_code.rating_save_success');

                return $this->sendResponse(true, $status_code, trans('message.rating_save_success'), $data);

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }

    }

    

    // Get Nearest Repaier List

    public function getRepairerList(Request $request){

        $userId = $request->user()->id;

        $data = array();

        $response_data = [];

        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }

        try{

            $validator = Validator::make($request->all(), [

                'languageCode' => 'required',

                'bookingId' => 'required|numeric',

            ]);

            if($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code, trans('message.invalid_input'));

            }

            $results = $this->BookingRepairerStatus->getBookingRepairerStatus($request->bookingId);

           



            if($results->isEmpty()) {

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,trans('message.exception_code'));

            }



            foreach ($results as $key => $value) {

                $isAbleToCall = ($value->status == '1') ? true : false;

                $isAbleToBook = ($value->status == '1') ? true : false;

                $response_data[$key]['repairerId'] = $value->repairerId;

                $response_data[$key]['repairerName'] =$value->first_name;

                $response_data[$key]['repairerImage'] = $value->photo;

                $response_data[$key]['repairerRating'] = Null;

                $response_data[$key]['repairerContactNo'] = $value->phone_number;

                $response_data[$key]['isAbleToCall'] =$isAbleToCall;

                $response_data[$key]['isAbleToBook'] =$isAbleToBook;

                $response_data[$key]['latitude'] = $value->latitude;

                $response_data[$key]['longitude'] = $value->longitude;

            }



            $status_code = config('response_status_code.data_fetched_success');

            return $this->sendResponse(true, $status_code, trans('message.data_fetched_success'), $response_data);

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $response_data);

        }

    }



    //Book a service by customer 24-06-2019

    public function bookService(Request $request){

        $userId = $request->user()->id;
        $now = Carbon::now()->format("Y-m-d h:m:s");
        $data = array();

        if(!Auth::guard('api')->check()) {
            $status_code = config('response_status_code.unauthorized_request');
            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));
        }

        try{
            $validator = Validator::make($request->all(), [
                'languageCode' => 'required',
                'bookingId' => 'required',
                'repairerId' => 'required',
            ]);

            if($validator->fails()) {
                $status_code = config('response_status_code.invalid_input');
                return $this->sendResponse(false, $status_code,trans('message.invalid_input'));

            }
            $booking = $this->Booking->bookingTypeUpdate($request->bookingId);
            $bookingRepairerStatus = $this->BookingRepairerStatus->saveBookingRepairerStatus($request->bookingId,$request->repairerId,$now);

            if($bookingRepairerStatus == 1){
                $status_code = config('response_status_code.data_save_successfully');
                return $this->sendResponse(true, $status_code, trans('message.data_save_successfully'));
            }
        }catch (Exception $e) {
            $status_code = config('response_status_code.exception_code');
            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);
        }      
    }



    public function getAllBrandServices(Request $request){

        $userId = $request->user()->id;

        $data = array();



        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }



        try{

            $validator = Validator::make($request->all(), [

                'languageCode' => 'required',

                'vehicleTypeId' => 'required',

            ]);

            

            if($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code,trans('message.invalid_input'));

            }

            $VehicleTypeServiceAssign =  $this->VehicleTypeServiceAssign->getVehicleServicesTypeWise($request->vehicleTypeId);

            

            if(count($VehicleTypeServiceAssign) > 0){

                foreach ($VehicleTypeServiceAssign as $key => $value) {

                    $data[$key]['brandName'] = $value->name;

                    $data[$key]['brandId']  = $value->vehicle_brand_id;

                    $query = $this->VehicleBrandMultipleServiceAssign->getMultipleServices($value->id);



                    if(count($query)){

                        foreach ($query as $key1 => $value1) {

                           $data[$key]['services'][$key1]['serviceId'] = $value1->vehicle_service_id;

                           $data[$key]['services'][$key1]['serviceName'] = $value1->name;

                        }

                    }

                }

            }

             $status_code = config('response_status_code.data_fetched_success');

            return $this->sendResponse(true, $status_code, trans('message.data_fetched_success'),$data);

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }     



    }

    

    public function getBookingDetail(Request $request){

        $userId = $request->user()->id;

        $response_data = [];

        $data = array();



        if(!Auth::guard('api')->check()) {

            $status_code = config('response_status_code.unauthorized_request');

            return $this->sendResponse(false, $status_code, trans('message.unauthorized_request'));

        }



        try{

            $validator = Validator::make($request->all(), [

                'languageCode' => 'required',

                'bookingId'=> 'required',

            ]);

            if($validator->fails()) {

                $status_code = config('response_status_code.invalid_input');

                return $this->sendResponse(false, $status_code,trans('message.invalid_input'));

            }



            $bookingDetail = $this->Booking->getBooking($request->bookingId);



            if(empty($bookingDetail)) {

                $status_code = config('response_status_code.exception_code');

                return $this->sendResponse(true, $status_code,trans('message.exception_code'));

            }

             

            $bookingProgress = $this->BookingRepairerStatus->fetchBookingRepairerStatus($request->bookingId);


            //echo "<pre>";print_r($bookingProgress->toArray());exit;
            $bookingStatusMain = 0;
            if(count($bookingProgress->toArray()) > 0){
                foreach ($bookingProgress as $key => $value) {

                    if($bookingStatusMain < $value['status']){
                       $bookingStatusMain = $value['status'];
                    }
                }
            }
            
            $bookingProgress = (empty($bookingProgress)) ? NULL : $bookingProgress;

                  

            $pickupDetails = $this->BookingRepairerStatus->getBookingRepairerStatuswise($request->bookingId); 

           

            if(!empty($pickupDetails)){

                $pickupName = $pickupDetails->name;

                $pickupStatusTime = $pickupDetails->status_time;

                $pickupPhoto = $pickupDetails->photo;

                $pickupMobileNumber = $pickupDetails->mobile_number;

            }else{

                $pickupName = "";

                $pickupStatusTime = "";

                $pickupPhoto = "";

                $pickupMobileNumber = "";

            }

           



            $services = $this->BookService->getBookingService($request->bookingId);



            $services = (empty($services)) ? NULL : $services;

            

            

            $response_data['bookingId'] = $request->bookingId;

            $response_data['repairerName'] = $bookingProgress[0]->first_name;

            $response_data['progress'] = $bookingProgress;

            $response_data['location'] = $bookingDetail->address;

            $response_data['latitude'] = $bookingDetail->latitude;

            $response_data['longitude'] = $bookingDetail->longitude;

            $response_data['vehicle'] = $bookingDetail->vehicleName;
            
            $response_data['vehicleMakeName'] = $bookingDetail->vehicleMakeName;

            $response_data['customerName'] = $bookingDetail->customerName;

            $response_data['customerNumber'] = $bookingDetail->customerNumber;

            $response_data['pickupTime'] = $pickupStatusTime;

            $response_data['bookingTypeStatus'] = $bookingDetail->type;

            $response_data['bookingStatus'] = $bookingStatusMain;

            $checkSubmitReview = $this->Booking->checkSubmitReview($request->bookingId,$userId);
            $reviewArray = array();

           if(!empty($checkSubmitReview['review'])){
                
                $explodeData = explode(',',$checkSubmitReview['review']['reviews']);
                foreach ($checkSubmitReview['review_type'] as $key1 => $value1) {
                    $reviewArray['review'][$key1]['id'] = $value1['id'];
                    $reviewArray['review'][$key1]['name'] = $value1['name'];
                    $reviewArray['review'][$key1]['value'] = $explodeData[$key1];
                }
                $reviewArray['averageReview'] = $checkSubmitReview['review']['average'];
           }


            $response_data['review'] = $reviewArray;

            $response_data['Services'] = $services;

            $response_data['charges'] = $bookingDetail->amount;

            $response_data['pickupBoyName'] = $pickupName;

            $response_data['LicenseImage'] = $pickupPhoto;

            $response_data['serviceRating'] = Null;

            $response_data['isPickUp'] = $bookingDetail->is_pickup;

            $response_data['PickupBoyNumber'] = $pickupMobileNumber;



            $status_code = config('response_status_code.data_save_successfully');

            return $this->sendResponse(true, $status_code, trans('message.data_save_successfully'),$response_data);

        }catch (Exception $e) {

            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);

        }     

    }
    public function multipleLanguageDemo(Request $request){
        $userId = $request->user()->id;

        $response_data = [];

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

           $languageCode = strtolower($request->languageCode);
           echo "<pre>";print_r($languageCode);exit;
           $this->VehicleType->getVehicleType($languageCode);
        }catch (Exception $e){
            $status_code = config('response_status_code.exception_code');

            return $this->sendResponse(true, $status_code,$e->getMessage(), $data);
        }
    }

}