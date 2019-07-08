<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Review;
use App\ReviewType;

class Booking extends Model
{
    protected $fillable = ['type','amount'];
    public function bookingTypeUpdate($bookingId){
    	$data = Booking::where("id",$bookingId)->update(['type' => "3"]);
    	return $data;
    }
    public function getBooking($bookingId){
    	$data = 	Booking::select("bookings.id","bookings.addressId","repairer_addresses.address","repairer_addresses.latitude","repairer_addresses.longitude","bookings.makeId","vehicle_makes.name as vehicleMakeName","bookings.userId","customer.first_name as customerName","customer.phone_number as customerNumber","bookings.type","bookings.amount","bookings.is_pickup","vehicle_types.type as vehicleName")
                    ->leftJoin("repairers as customer","customer.id","=","bookings.userId")
                    ->leftJoin("repairer_addresses","repairer_addresses.id","=","bookings.addressId")
                    ->leftJoin("vehicle_makes","vehicle_makes.id","=","bookings.makeId")
                    ->leftJoin("vehicle_types","vehicle_types.id","=","bookings.vehicleTypeId")
                    ->where("bookings.id",$bookingId)->first();
        return $data;
    }
    public function saveBooking($userId,$makeId,$input){
            $booking = new Booking();
            $booking->userId = $userId;
            $booking->addressId =  $input['addressId'];
            $booking->isAuthorizeServiceCenter = $input['isAuthorizeServiceCenter'];
            if($input['isAuthorizeServiceCenter'] == 0){
                $booking->makeId = $makeId;
            }
            $booking->is_pickup =$input['isSelectPickupTime'];
            if($input['isSelectPickupTime'] == 1){
                $booking->is_pickup = $input['isSelectPickupTime'];
                $booking->type = "0";
            }else{
                $booking->type = "1";
            }
            $booking->brandId = $input['brandId'];
            $booking->vehicleTypeId = $input['vehicleTypeId'];
            $booking->save();
            return $booking;
    }
    public function getBookingListUser($userId,$status){
        if($status == "0"){

            $data =  Booking::select("bookings.id as bookingId","bookings.addressId","repairer_addresses.address","repairer_addresses.latitude","repairer_addresses.longitude","vehicle_types.type as vehicleName","bookings.userId","customer.first_name as repairerFirstName","customer.last_name as repairerlastName","customer.garageName as garageName","customer.phone_number as repairerNumber","customer.photo as repairerImage","customer.id as repairerId","bookings.amount as cost","booking_repairer_statuses.status","booking_repairer_statuses.status_time as dateAndTime","booking_repairer_statuses.id as brsid","vehicle_brands.name as brandName","customer.garage_photo as garagePhotos")
                    ->leftJoin("booking_repairer_statuses","booking_repairer_statuses.booking_id","=",\DB::raw("bookings.id"))
                    ->leftJoin("repairers as customer","customer.id","=","booking_repairer_statuses.repairer_id")
                    ->leftJoin("vehicle_brands","vehicle_brands.id","=","bookings.brandId")
                    ->leftJoin("repairer_addresses","repairer_addresses.id","=","bookings.addressId")
                    ->leftJoin("vehicle_types","vehicle_types.id","=","bookings.vehicleTypeId")
                    
                    ->where("bookings.userId",$userId)->whereIn("booking_repairer_statuses.status",array("2","3","4","5","6","7"))
                    ->groupBy('booking_repairer_statuses.booking_id')
                    ->orderby('booking_repairer_statuses.id', 'DESC')
                    ->whereRaw('booking_repairer_statuses.id = (select max(`id`) from booking_repairer_statuses WHERE booking_id = bookings.id)')
                    ->orderby("bookings.id","DESC")
                    ->get()->toArray();

            $data1 =  Booking::select("bookings.id as bookingId","bookings.addressId","repairer_addresses.address","repairer_addresses.latitude","repairer_addresses.longitude","vehicle_types.type as vehicleName","bookings.userId","customer.first_name as repairerFirstName","customer.last_name as repairerlastName","customer.garageName as garageName","customer.phone_number as repairerNumber","customer.photo as repairerImage","customer.id as repairerId","bookings.amount as cost","booking_repairer_statuses.status","booking_repairer_statuses.status_time as dateAndTime","booking_repairer_statuses.id as brsid","vehicle_brands.name as brandName","customer.garage_photo as garagePhotos")
                    ->leftJoin("booking_repairer_statuses","booking_repairer_statuses.booking_id","=",\DB::raw("bookings.id"))
                    ->leftJoin("repairers as customer","customer.id","=","booking_repairer_statuses.repairer_id")
                    ->leftJoin("repairer_addresses","repairer_addresses.id","=","bookings.addressId")
                    ->leftJoin("vehicle_brands","vehicle_brands.id","=","bookings.brandId")
                    ->leftJoin("vehicle_types","vehicle_types.id","=","bookings.vehicleTypeId")
                    
                    ->where("bookings.userId",$userId)->whereIn("booking_repairer_statuses.status",array("0","1"))
                    ->orderby("bookings.id","DESC")
                    // ->groupBy('booking_repairer_statuses.booking_id')
                    // ->orderby('booking_repairer_statuses.id', 'DESC')
                    // ->whereRaw('booking_repairer_statuses.id = (select max(`id`) from booking_repairer_statuses WHERE booking_id = bookings.id)')
                    ->get()->toArray();

               // echo "<pre>";print_r($data);echo "<pre>";print_r($data1);exit;
                $mainData = array();
                $keyArray = array();
                if(!empty($data)){
                    foreach ($data as $key => $value) {
                        $keyArray[] = $value['bookingId'];
                    }
                }
               
                if(!empty($data1)){
                    foreach ($data1 as $key1 => $value1) {
                        if(in_array($value1['bookingId'],$keyArray)){

                        }else{
                            array_push($data,$value1);
                        }
                    }
                }
                if(count($data) > 0){
                    $sortArray = array(); 
                    foreach($data as $person){ 
                        foreach($person as $key=>$value){ 
                            if(!isset($sortArray[$key])){ 
                                $sortArray[$key] = array(); 
                            } 
                            $sortArray[$key][] = $value; 
                        } 
                    } 

                    $orderby = "bookingId"; //change this to whatever key you want from the array 

                    array_multisort($sortArray[$orderby],SORT_DESC,$data); 

                    foreach ($data as $key => $value) {
                        $photos = str_replace("\\", "", $value['garagePhotos']);
                       $data[$key]['garagePhotos'] = json_decode($photos);
                    }
                }

               
            return $data;
        }else{
            $data =  Booking::select("bookings.id as bookingId","bookings.addressId","repairer_addresses.address","repairer_addresses.latitude","repairer_addresses.longitude","vehicle_types.type as vehicleName","bookings.userId","customer.first_name as repairerFirstName","customer.last_name as repairerlastName","customer.garageName as garageName","customer.phone_number as repairerNumber","customer.photo as repairerImage","customer.id as repairerId","bookings.amount as cost","booking_repairer_statuses.status","booking_repairer_statuses.status_time as dateAndTime","vehicle_brands.name as brandName","customer.garage_photo as garagePhotos")
                   ->leftJoin("booking_repairer_statuses","booking_repairer_statuses.booking_id","=","bookings.id")
                    ->leftJoin("repairers as customer","customer.id","=","booking_repairer_statuses.repairer_id")
                    ->leftJoin("repairer_addresses","repairer_addresses.id","=","bookings.addressId")
                    ->leftJoin("vehicle_brands","vehicle_brands.id","=","bookings.brandId")
                    ->leftJoin("vehicle_types","vehicle_types.id","=","bookings.vehicleTypeId")
                    ->where("bookings.userId",$userId)->where("booking_repairer_statuses.status","8")
                    ->orderby("bookings.id","DESC")->get()->toArray();
                    if(count($data) > 0){
                        foreach ($data as $key => $value) {
                            $reviewArray = array();
                            $photos = str_replace("\\", "", $value['garagePhotos']);
                           $data[$key]['garagePhotos'] = json_decode($photos);
                           $checkSubmitReview = $this->checkSubmitReview($value['bookingId'],$userId);
                           
                           if(!empty($checkSubmitReview['review'])){
                                
                                $explodeData = explode(',',$checkSubmitReview['review']['reviews']);
                                foreach ($checkSubmitReview['review_type'] as $key1 => $value1) {
                                    $reviewArray['review'][$key1]['id'] = $value1['id'];
                                    $reviewArray['review'][$key1]['name'] = $value1['name'];
                                    $reviewArray['review'][$key1]['value'] = $explodeData[$key1];
                                }
                                $reviewArray['averageReview'] = $checkSubmitReview['review']['average'];
                                $data[$key]['review'] = $reviewArray;
                           }else{
                            $data[$key]['review'] = $reviewArray;
                           }
                           
                        }
                    }
                    echo "<pre>";print_r($data);exit;
            return $data;
        }
    }
    public function checkSubmitReview($bookingId,$userId){
        $data = Review::where('booking_id',$bookingId)->first();
        $data1 = ReviewType::get()->toArray();
        $return = array();
        $return['review'] = $data;
        $return['review_type'] = $data1;
        return $return;
    }
}
