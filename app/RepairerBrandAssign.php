<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Service;
use App\VehicleTypeServiceAssign;
use App\VehicleBrandMultipleServiceAssign;
use App\RepairerServiceAssign;

class RepairerBrandAssign extends Model
{
   

    public function getRepairerBrands($userId){
	    $data = RepairerBrandAssign::select("vbrand.id","vbrand.name","repairer_brand_assigns.id as repairerBrandAssignsId","vtype.id as VehicleId", "vtype.type as vehicleName")
               ->join("vehicle_brands as vbrand","vbrand.id","=","vehicle_brand_id")
               ->join("vehicle_types as vtype","vtype.id","=","vehicle_type_id")
               ->where("repairer_id", $userId)->get();
        return $data;
    }
    public function saveRepairerBrandAssign($userId,$vehicleId,$brandId){
    			 	$repairerTypeBrandAssign = new RepairerBrandAssign();
                    $repairerTypeBrandAssign->repairer_id = $userId;
                    $repairerTypeBrandAssign->vehicle_type_id = $vehicleId;
                    $repairerTypeBrandAssign->vehicle_brand_id = $brandId;
                    $repairerTypeBrandAssign->save();
                    return $repairerTypeBrandAssign;
    }

    public function saveRepairerBrandServices($userId,$request){
        $repairerTypeBrandAssignSaved = 0;
        $vehicleBrandMultipleService = 0;
        $repairerServiceAssign = 0;
        $allSaved = 0;
        $repairerTypeBrandAssignDelete = RepairerBrandAssign::where("repairer_id",$userId)->delete();
        $repairerServiceAssignDelete = RepairerServiceAssign::where("repairer_id",$userId)->delete();
        if($request->brands){
            foreach ($request->brands as $key => $value) {
                //Save repairer brands starts
                    $repairerTypeBrandAssign = new RepairerBrandAssign();
                    $repairerTypeBrandAssign->repairer_id = $userId;
                    $repairerTypeBrandAssign->vehicle_type_id = $request->vehicleId;
                    $repairerTypeBrandAssign->vehicle_brand_id = $value['brandId'];
                    $repairerTypeBrandAssignSaved = $repairerTypeBrandAssign->save();
                //Save repairer brands ends
                if(array_key_exists('services',$value)){
                    foreach ($value['services'] as $k => $v) {
                        if($v['id'] == 0){
                            $services = Service::saveServices($v['name'],$userId);
                            $vehicleTypeServiceAssign = VehicleTypeServiceAssign::getVehicleTypeServiceId($request->vehicleId,$value['brandId']);
                                $vehicleBrandMultipleService = VehicleBrandMultipleServiceAssign::saveMultipleServices($vehicleTypeServiceAssign->id,$services->id);
                                $sId = $services->id;
                           
                        }else{
                            $vehicleBrandMultipleService = 1;
                            $sId = $v['id'];
                        }
                        $repairerServiceAssign = RepairerServiceAssign::saveRepairerServices($userId,$repairerTypeBrandAssign->id,$sId);
                    }
                }
            }
        }

        $allSaved = ($repairerTypeBrandAssignSaved == 1  && $vehicleBrandMultipleService == 1 && $repairerServiceAssign == 1) ? 1 : 0;
       return $allSaved;
       
    }
}
