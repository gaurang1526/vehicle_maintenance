<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Review extends Model
{
	public function saveReview($bookingId,$reviewTypeId,$rating,$feedbackMessage){
	    $Review = new Review();
	    $Review->booking_id = $bookingId;
	    $Review->reviews = $reviewTypeId;
	    $Review->average = $rating;
	    $Review->feedback_message = $feedbackMessage;
	    $Review->save();
	}
}
