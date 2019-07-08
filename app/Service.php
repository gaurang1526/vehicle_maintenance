<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use TCG\Voyager\Traits\Translatable;
use Illuminate\Support\Facades\DB;

class Service extends Model
{
    use Translatable;
    protected $translatable = ['name'];
    
    public static function saveServices($name,$userId){
    	$servicesnSaved = 0;
    	$services = new Service();
        $services->name = $name;
        $services->create_by = $userId;
        $servicesnSaved = $services->save();
        return $services;
    }
    
    public function getServices($vehicleTypeId,$brandId,$userId){
    	 $data = DB::table('vehicle_type_service_assigns AS vtsa')
					->select("s.name as serviceName","s.id as serviceId")
					->leftJoin("vehicle_brand_multiple_service_assigns as vbmsa",'vtsa.id','vbmsa.vehicle_type_service_assigns_id')
					->leftJoin('services as s','vbmsa.service_id','=','s.id')
					->where('vtsa.vehicle_type_id',$vehicleTypeId)
					->where('vtsa.vehicle_brand_id',$brandId)
					->where('s.create_by',0)
					->orWhere('s.create_by',$userId)
					->orderBy('serviceId', 'ASC')
					->get()->toArray(); 
         return $data;
    }
    public function customServiceCreate($serviceName,$userId){
		$Service = new Service();
        $Service->name = $serviceName;
        $Service->create_by = $userId;
        $Service->save();
        return $Service;
    }
}
