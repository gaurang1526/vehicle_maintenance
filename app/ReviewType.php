<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class ReviewType extends Model
{
    public function getReviewType(){
    	$data = ReviewType::select('id','name')->get()->toArray();
    	return $data;
    }
}
