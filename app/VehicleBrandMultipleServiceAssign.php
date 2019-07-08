<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VehicleBrandMultipleServiceAssign extends Model
{
    public function getMultipleServices($id){
    	$data = DB::table('vehicle_brand_multiple_service_assigns AS vbmsa')
                ->select("s.name","s.id as vehicle_service_id")
                ->leftJoin('services as s','vbmsa.service_id','=','s.id')
                ->where('vbmsa.vehicle_type_service_assigns_id',$id)
                ->get()->toArray(); 
        return $data;
    }


    public static function saveMultipleServices($vehicle_type_service_assigns_id,$service_id ){
        $vehicleTypeBrandServiceSaved = 0;
    	$vehicleTypeBrandService = new VehicleBrandMultipleServiceAssign();
        $vehicleTypeBrandService->vehicle_type_service_assigns_id = $vehicle_type_service_assigns_id;
        $vehicleTypeBrandService->service_id = $service_id;
        $vehicleTypeBrandServiceSaved = $vehicleTypeBrandService->save();
        return $vehicleTypeBrandServiceSaved;
    }
    
    public function saveVehicleBrandMultipleServiceAssign($VehicleTypeServiceAssignId,$serviceId){
    	$VehicleBrandMultipleServiceAssign = new VehicleBrandMultipleServiceAssign();
        $VehicleBrandMultipleServiceAssign->vehicle_type_service_assigns_id = $VehicleTypeServiceAssignId;
        $VehicleBrandMultipleServiceAssign->service_id = $serviceId;
        $VehicleBrandMultipleServiceAssign->save();
        return $VehicleBrandMultipleServiceAssign;

    }
}
