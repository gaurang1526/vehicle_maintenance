<?php



use Illuminate\Http\Request;



/*

|--------------------------------------------------------------------------

| API Routes

|--------------------------------------------------------------------------

|

| Here is where you can register API routes for your application. These

| routes are loaded by the RouteServiceProvider within a group which

| is assigned the "api" middleware group. Enjoy building your API!

|

*/

Route::group([

    'prefix' => 'auth'

], function () {

    Route::post('login', 'API\AuthController@login');

});





Route::middleware('auth:api')->get('/user', function (Request $request) {

    return $request->user();

});





//Repairer Api Routes starts

	Route::post("repairerLogin","API\RepairerApiController@repairerLogin");

	Route::post('repairerLogin/verify/otp', 'API\RepairerApiController@repairerLoginVerifyOTP');



	Route::middleware(['auth:api'])->group(function () {

		Route::post('logout','API\RepairerApiController@logout');
		Route::post('get/user-type','API\RepairerApiController@getUserType');
		
		Route::post('get/repairer/profile', 'API\RepairerApiController@getRepairerProfile');
		Route::post('get/vehicle-type', 'API\CustomerApiController@getVehicleType');

		Route::post('get/brand-list', 'API\CustomerApiController@getBrandList');
		Route::post('get/review-types', 'API\CustomerApiController@getReviewTypes');

		Route::post('get/service-list', 'API\CustomerApiController@getServiceList');

		Route::post('get/team-member-list', 'API\RepairerController@getTeamMemberList');

		Route::post('saveSearchRepairs', 'API\CustomerApiController@saveSearchRepairs');

		Route::post('get/save-address-list', 'API\CustomerApiController@getSaveAddressList');

		Route::post('get/make-list','API\CustomerApiController@getMakeList');



		Route::post('save/review','API\CustomerApiController@saveReview');

		Route::post('get/teamMembers', 'API\RepairerApiController@getTeamMembers');

		Route::post('add/teamMember', 'API\RepairerApiController@addTeamMember');

		Route::post('add/repairer/profile', 'API\RepairerApiController@addRepairerProfile');
		Route::post('get/booking/listUser', 'API\RepairerApiController@getBookingListUser');
		Route::post('save/address', 'API\RepairerApiController@saveAddress');
		Route::post('delete/address', 'API\RepairerApiController@deleteAddress');
		Route::post('delete/teamMember', 'API\RepairerApiController@deleteTeamMember');
		Route::post('get/repairer/bookingList', 
			'API\RepairerApiController@getRepairerBookingList');

		Route::post('get/repairerList/', 'API\CustomerApiController@getRepairerList');
		Route::post('change/bookingStatus/', 'API\RepairerApiController@changeBookingStatus');

		Route::post('book/service/', 'API\CustomerApiController@bookService');


		Route::post('get/all-brand-services','API\CustomerApiController@getAllBrandServices');
		Route::post('get/lastBooking/detail/', 'API\RepairerApiController@getLastBookingDetail');
		Route::post('get/booking/detail/', 'API\CustomerApiController@getBookingDetail');

		Route::post('add/repairer/profileData', 'API\RepairerApiController@addRepairerProfileData');
		
		Route::post('add/repairer/profileImage', 'API\RepairerApiController@addRepairerProfileImage');

		Route::post('mutliple-language','API\CustomerApiController@multipleLanguageDemo');

		
	});

//Repairer Api Routes  Ends