<?php

namespace App\Http\Controllers\Voyager;

use Illuminate\Http\Request;
use TCG\Voyager\Facades\Voyager;
use TCG\Voyager\Http\Controllers\VoyagerUserController;
use App\UserCityAssign;
use App\City;

class UsersController extends VoyagerUserController
{
    public function profile(Request $request)
    {
        return Voyager::view('voyager::profile');
    }

    // POST BR(E)AD
    public function update(Request $request, $id)
    {
        if (app('VoyagerAuth')->user()->getKey() == $id) {
            $request->merge([
                'role_id'                              => app('VoyagerAuth')->user()->role_id,
                'user_belongsto_role_relationship'     => app('VoyagerAuth')->user()->role_id,
                'user_belongstomany_role_relationship' => app('VoyagerAuth')->user()->roles->pluck('id')->toArray(),
            ]);
        }

        return parent::update($request, $id);
    }

    public function getParentCity(Request $request){
        $parentCities = UserCityAssign::join('cities', 'user_city_assigns.city_id', '=', 
            'cities.id')->where("user_id",$request->parent_id)
            ->select('cities.id','cities.name')->get();

        if($parentCities->isEmpty()){
           $parentCities = City::select('cities.id','cities.name')->get();     
        }
        echo json_encode($parentCities);
    }
}
