<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class VehicleMake extends Model
{
    public function getMakeList($vehicleTypeId,$vehicleBrandId){
    	$data = DB::table('vehicle_type_brand_make_assigns AS vtbma')
                    ->select('vm.id as makeId','vm.Name as makeName')
                    ->leftJoin('vehicle_makes as vm','vtbma.vehicle_make_id','=','vm.id')
                    ->where('vtbma.vehicle_type_id',$vehicleTypeId)
                    ->where('vtbma.vehicle_brand_id',$vehicleBrandId)
                    ->get()->toArray(); 
        return $data;
    }
    public function saveMake($makeName){
       $VehicleMake = new VehicleMake();
       $VehicleMake->name = $makeName;
       $VehicleMake->save();
       return $VehicleMake;
    }
}
