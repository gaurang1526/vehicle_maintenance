<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/



Route::post('/get_vehicle_details', 
	['uses' => 'Voyager\RepairersController@getVehicleDetails',   
	'as' => 'getVehicleDetails']);


Route::group(['prefix' => 'admin'], function () {
    Voyager::routes();
    Route::post('/get_parent_city',['uses' => 'Voyager\UsersController@getParentCity','as'=> 'get_parent_city']);
});


Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::post('/getBrandlist','Voyager\VehicleTypeBrandMakeAssignsController@getBrandlist');
Route::post('/getServiceList','Voyager\VehicleTypeBrandMakeAssignsController@getServiceList');
Route::get('/getBookingStatus','Voyager\BookingsController@getBookingStatusList')->name('getBookingStatus');
