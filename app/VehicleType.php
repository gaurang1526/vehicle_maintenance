<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;
use Illuminate\Support\Facades\DB;

class VehicleType extends Model
{
    use Translatable;
    protected $translatable = ['type'];
    public function getVehicleType(){
	    $data = DB::select("select id as vehicleTypeId,type as vehicleName,image_path as vehicleImage, NULL as isFrequentlyUsed from vehicle_types");
	    return $data;
    }
    public function mostUseVehicleUserWise($userId){
	    $data = DB::select('select count(vehicleTypeId) as totalcontracts , vehicleTypeId from bookings group by vehicleTypeId HAVING count(vehicleTypeId) = (Select Max(cnt) From (Select count(vehicleTypeId) cnt From bookings where userId = '.$userId.' Group By vehicleTypeId) z)');
	    return $data;
    }
}
