<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class RepairerServiceAssign extends Model
{
    public function getRepairerServices($repairerBrandAssignsId,$userId){
	    $data = RepairerServiceAssign::select("services.id as serviceId","services.name as serviceName")
	            ->join("services as services","services.id","=","service_id")
	            ->where("repairer_id", $userId)
                ->where('repairer_brand_assigns_id',$repairerBrandAssignsId)->get();
	    return $data;
    }

    public static function saveRepairerServices($userId,$repairer_brand_assigns_id,$sId){
        
        $repairerServiceAssignSaved = 0;
		$repairerServiceAssign = new RepairerServiceAssign();
        $repairerServiceAssign->repairer_id = $userId;
        $repairerServiceAssign->repairer_brand_assigns_id = $repairer_brand_assigns_id;
        $repairerServiceAssign->service_id = $sId;
        $repairerServiceAssignSaved = $repairerServiceAssign->save();
		return $repairerServiceAssignSaved;
    }
}
