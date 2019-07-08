<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class RepairerWorkingDay extends Model
{
    public function saveRepairerWorkingDays($userId,$request){
        $workingDaySaved = 0;
        $workingDayDelete = RepairerWorkingDay::where("repairer_id",$userId)->delete();
        if($request->workingDays){
        	foreach ($request->workingDays as $key => $value) {
        		$workingDay = new RepairerWorkingDay();
        		$workingDay->day = $value['day'];
        		$workingDay->start_time	 = $value['startTime'];
        		$workingDay->end_time = $value['endTime'];
        		$workingDay->repairer_id = $userId;
        		$workingDaySaved = $workingDay->save();
        	}
        }
    	return $workingDaySaved;
    }

    public function getRepairerWorkingDays($userId){
        $data = RepairerWorkingDay::where("repairer_id", $userId)->get();
        return $data;
    }
}
