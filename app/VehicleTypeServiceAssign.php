<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VehicleTypeServiceAssign extends Model
{
    public function getVehicleServicesTypeWise($vehicleTypeId){
    	$data = DB::table('vehicle_type_service_assigns AS vtsa')
                ->select("vtsa.id","vbrand.name","vbrand.id as vehicle_brand_id")
                ->leftJoin('vehicle_brands as vbrand','vtsa.vehicle_brand_id','=','vbrand.id')
                ->where('vtsa.vehicle_type_id',$vehicleTypeId)
                ->get()->toArray(); 
        return $data;
    }


    public static function getVehicleTypeServiceId($vehicleId,$brandId){
		$data = VehicleTypeServiceAssign::where("vehicle_type_id",$vehicleId)->where("vehicle_brand_id",$brandId)->first();
		return $data;
    }
    public function getVehicleTypeServiceAssign($input){
    	$VehicleTypeServiceAssign = VehicleTypeServiceAssign::where('vehicle_type_id',$input['vehicleTypeId'])->where('vehicle_brand_id',$input['brandId'])->first();
    	return $VehicleTypeServiceAssign;
    }
    public function saveVehicleTypeServiceAssign($input){
    	$VehicleTypeServiceAssign = new VehicleTypeServiceAssign();
        $VehicleTypeServiceAssign->vehicle_type_id = $input['vehicleTypeId'];
        $VehicleTypeServiceAssign->vehicle_brand_id = $input['brandId'];
        $VehicleTypeServiceAssign->save();
        return $VehicleTypeServiceAssign;
    }
}
