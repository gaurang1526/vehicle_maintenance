<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class VehicleTypeBrandMakeAssign extends Model
{
    public function saveVehicleTypeBrandMakeAssign($vehicleTypeId,$brandId,$VehicleMakeId){
       $VehicleTypeBrandMakeAssign = new VehicleTypeBrandMakeAssign();
       $VehicleTypeBrandMakeAssign->vehicle_type_id = $vehicleTypeId;
       $VehicleTypeBrandMakeAssign->vehicle_brand_id = $brandId;
       $VehicleTypeBrandMakeAssign->vehicle_make_id = $VehicleMakeId;
       $VehicleTypeBrandMakeAssign->save();
       return $VehicleTypeBrandMakeAssign;
    }
}
