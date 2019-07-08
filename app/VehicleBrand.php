<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;
use Illuminate\Support\Facades\DB;

class VehicleBrand extends Model
{
    use Translatable;
    protected $translatable = ['name'];
    public function getBrandList($vehicleTypeId){
		$data = DB::table('vehicle_type_brand_assigns AS vtba')
		        ->select("vb.name","vb.id as brandId")
		        ->leftJoin('vehicle_brands as vb','vtba.vehicle_brand_id','=','vb.id')
		        ->where('vtba.vehicle_type_id',$vehicleTypeId)
		        ->orderBy('brandId', 'ASC')
	        	->get()->toArray();
	    return $data;
	}
	public function getVehicleBrand($brandId){
		$data = VehicleBrand::where('id', $brandId)->first();
		return $data;
	}
}
