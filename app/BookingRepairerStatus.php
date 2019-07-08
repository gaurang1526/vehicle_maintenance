<?php



namespace App;



use Illuminate\Database\Eloquent\Model;





class BookingRepairerStatus extends Model

{

    public function getBookingRepairerStatus($bookingId){

    	$results =  BookingRepairerStatus::select("repairers.id as repairerId",

	                "repairers.first_name","repairers.photo","repairers.phone_number", 

	                "repairers.is_repairer", "repairer_addresses.id as addressId",

	                "repairer_addresses.address","repairer_addresses.latitude",

	                "repairer_addresses.longitude","repairer_addresses.address_type","booking_repairer_statuses.status")

	               ->leftJoin("repairers", "booking_repairer_statuses.repairer_id","=","repairers.id")

	               ->leftJoin("repairer_addresses", "booking_repairer_statuses.address_id","=","repairer_addresses.id")

	               ->where("booking_id",$bookingId)->get();

	    return $results;

    }



    public function saveBookingRepairerStatus($bookingId,$repairerId,$now){
        $addressId = BookingRepairerStatus::where("booking_id", $bookingId)->where("repairer_id", $repairerId)->first();

      
        $bookingRepairerStatusSaved = 0;

    	$bookingRepairerStatus = new BookingRepairerStatus(); 

        $bookingRepairerStatus->booking_id = $bookingId;

        $bookingRepairerStatus->repairer_id = $repairerId;
        $bookingRepairerStatus->address_id = $addressId->address_id;

        $bookingRepairerStatus->status = "3";

        $bookingRepairerStatus->status_time = $now;

        $bookingRepairerStatusSaved = $bookingRepairerStatus->save();

        $bookingRepairerStatus = BookingRepairerStatus::where("booking_id", $bookingId)->where("repairer_id","!=", $repairerId)->delete();

        return $bookingRepairerStatusSaved;

    }



    public function fetchBookingRepairerStatus($bookingId){

        $data = BookingRepairerStatus::select("status","status_time","repairers.first_name")

                            ->leftJoin("repairers","repairers.id","=","booking_repairer_statuses.repairer_id")

                            ->where("booking_id",$bookingId)->get();

        return $data;

    }



    public function getBookingRepairerStatuswise($bookingId){

       $data = BookingRepairerStatus::select("repairer_teams.name","booking_repairer_statuses.status_time","repairer_teams.photo","repairer_teams.mobile_number")

        ->leftJoin("repairer_teams","repairer_teams.id","=","booking_repairer_statuses.pickupboy_id")

        ->where("booking_id",$bookingId)->where("status","4")->first();

        return $data ;

    }



    public function saveMultipleBookingRepairerStatus($bookingId,$value){

        

        $bookingRepairerStatus = new BookingRepairerStatus();

        $bookingRepairerStatus->booking_id =  $bookingId;

        $bookingRepairerStatus->address_id = $value->addressId;

        $bookingRepairerStatus->repairer_id = $value->repairerId;

        $bookingRepairerStatus->status = "0";

        $bookingRepairerStatus->save();

        return $bookingRepairerStatus;

    }



    public function getRepairerBookingListDetails($bookingListType,$userId){
        $bookingListType = (string)$bookingListType;
        $bookingDetails = BookingRepairerStatus::select("bookings.id as BookingId",

                    "bookings.userId","repairers.first_name","repairers.photo",

                    "repairer_addresses.address","bookings.type",

                    "vehicle_makes.name as makeName","bookings.created_at","booking_repairer_statuses.status","vehicle_brands.id as brandId","vehicle_brands.name as brandName")

            ->leftJoin("bookings","booking_repairer_statuses.booking_id","=","bookings.id")

            ->leftJoin("repairers","bookings.userId","=","repairers.id")

            ->leftJoin("repairer_addresses","bookings.addressId","=","repairer_addresses.id")

            ->leftJoin("vehicle_makes","bookings.makeId","=","vehicle_makes.id")

            ->leftJoin("vehicle_brands","bookings.brandId","=","vehicle_brands.id")

            ->where("booking_repairer_statuses.repairer_id",$userId)->where("booking_repairer_statuses.status",$bookingListType)->get();

        return $bookingDetails;

    }

    public function getStatusOfBooking($bookingId,$userId){
        $bookingDetails = BookingRepairerStatus::select("status")->where("booking_id",$bookingId)->where("repairer_id",$userId)->first();
        return $bookingDetails;
    }

    public function updateBookingStatus($request,$userId){

        $bookingStatusSaved = 0;



        //Get Address ID Starts

            $addressId = BookingRepairerStatus::where("booking_id", $request->bookingId)->where("repairer_id", $userId)->first();
            $addressId = ($addressId) ? $addressId->address_id : Null;

        // Get Address Id Ends

        if($request->status == 1 ){
            $booking = Booking::find($request->bookingId)->update(['type' => "2"]);
        }

        if($request->status == 1 || $request->status == 2){
            $bookingRepairerStatus = BookingRepairerStatus::where("booking_id", $request->bookingId)->where("repairer_id", $userId)->first();
        }else{
            $bookingRepairerStatus = new BookingRepairerStatus(); 
        }

        $bookingRepairerStatus->booking_id = $request->bookingId;

        $bookingRepairerStatus->repairer_id = $userId;

        $bookingRepairerStatus->address_id = $addressId;

        $bookingRepairerStatus->status = $request->status;

        $bookingRepairerStatus->status_time = $request->statusTime;



        if($request->status == 4){

            $bookingRepairerStatus->pickupboy_id = $request->pickupboyId;

        }

        if($request->status == 7){

            $bookingRepairerStatus->deliveryboy_id = $request->deliveryBoyId;

            $bookingId = Booking::find($request->bookingId)->update(['amount' => $request->amount]);

        }

        if($request->status == 8){

            $booking = Booking::find($request->bookingId)->update(['type' => "4"]);

            $bookingRepairerStatus1 = BookingRepairerStatus::where("booking_id", 

            $request->bookingId)->where("repairer_id","!=", $userId)->where("status","1")

            ->delete();

        }

        $bookingStatusSaved = $bookingRepairerStatus->save();

        return $bookingStatusSaved;

    }



    public function getLastBookingDetails($userId){

        $bookingRepairerStatus = BookingRepairerStatus::where("repairer_id",$userId)

                            ->orderBy('id', 'DESC')->first();

        return $bookingRepairerStatus;

    }

}

