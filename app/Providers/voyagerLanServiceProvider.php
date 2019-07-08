<?php
namespace App\Providers;

use Config;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;

class voyagerLanServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        if (\Schema::hasTable('languages')) {
            $result = array();
            $config = array();
            $qur_result_val = DB::table('languages')->select('name')->get()->toArray();
            if(count($qur_result_val) > 0){
                foreach ($qur_result_val as $key => $value) {
                    $result[] = $value->name;
                }
            }
            //echo "<pre>";print_r($result);exit;
            Config::set('voyager.multilingual.locales', $result);
        }
    }
}
