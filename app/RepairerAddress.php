<?php



namespace App;



use Illuminate\Database\Eloquent\Model;

use TCG\Voyager\Traits\Translatable;

use Illuminate\Support\Facades\DB;



class RepairerAddress extends Model

{

    use Translatable;

    protected $translatable = ['address'];





    public function getRepairerAddress($userId){

    	$data = RepairerAddress::select('id as addressId','address_type as addressType','latitude','longitude','address')->where('repairer_id',$userId)->get()->toArray();

    	return $data;



    }

   

    

    public function saveRepairerAddress($userId,$repairerAddress,$request){

        $repairerAddressSaved = 0;

        $cityId = (!$request->cityId) ? 0 : $request->cityId;

        //$addressType = (!$request->addressType) ? 1 : $request->addressType;

        $repairerAddress->repairer_id = $userId;

        $repairerAddress->address = $request->address;

        $repairerAddress->city_id = $cityId;

        $repairerAddress->latitude = $request->latitude;

        $repairerAddress->longitude = $request->longitude;

        $repairerAddress->address_type = $request->addressType;

        $repairerAddressSaved = $repairerAddress->save();

        return $repairerAddressSaved;

           

    }



    public function getRepairerAddressBookingIdWise($bookingId){

        $bookingAddress =  RepairerAddress::select("bookings.addressId as addressId" ,

            "repairer_addresses.latitude" , "repairer_addresses.longitude")

         ->leftJoin("bookings","repairer_addresses.id","=","bookings.addressId")

         ->where("bookings.id", $bookingId)->first();

        return $bookingAddress;



    }

    public function getRepairerAddressIdWise($addressId,$latitude,$longitude){

        $results = RepairerAddress::select(DB::raw("repairers.id as repairerId,

                        repairers.is_repairer, repairer_addresses.id as addressId,

                        repairer_addresses.address_type,ROUND( 6371.0 * ACOS( SIN( $latitude *PI()/180 ) * SIN( repairer_addresses.latitude*PI()/180 )+ COS( $latitude *PI()/180 ) * COS( repairer_addresses.latitude*PI()/180 ) *  COS( (repairer_addresses.longitude*PI()/180) - ($longitude *PI()/180) )   ), 1) AS distance"))

                        ->leftJoin("repairers", "repairer_addresses.repairer_id","=","repairers.id")

                        ->where("repairer_addresses.id", "!=", $addressId)

                        ->where("repairers.is_repairer", "=", 1)

                        ->where("repairer_addresses.address_type", "=", "4")

                        ->havingRaw('distance < ?', [5])

                        ->get();

        return $results;

    }

}

