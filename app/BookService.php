<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class BookService extends Model
{
    public function getBookingService($bookingId){
    	$data = BookService::select("services.id as serviceId","services.name as serviceName")
                      ->leftJoin("services","services.id","=","book_services.service_id")
                      ->where("booking_id",$bookingId)->get();
        return $data;
    }
    public function saveBookService($bookingId,$serviceId){
    	$BookService = new BookService();
        $BookService->booking_id = $bookingId;
        $BookService->service_id = $serviceId;
        $BookService->save();
        return $BookService;
    }
}
