<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;

use \Validator;
use Illuminate\Support\Facades\Schema;

use finfo;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        app('view')->composer('*', function ($view) {

            $request = app(\Illuminate\Http\Request::class);

            $isAjaxRequest = false;
            if($request->ajax()){
                $isAjaxRequest = true;
            }
            if ($appRoute = app('request')->route()) {


                $action = $appRoute->getAction();
                $currentUrl = app('request')->url();
                $roleId = (!empty(Auth::user()->role_id)) ? Auth::user()->role_id : 0;
                $userId = (!empty(Auth::user()->id)) ? Auth::user()->id : 0;

                if (!empty($action['controller'])) {
                    $controller = (class_basename($action['controller'])) ? class_basename($action['controller']) : 'HomeController@index';
                    list($controller, $action) = explode('@', $controller);
                } else {
                    $controller = "HomeController";
                    $action = "index";
                }

                $view->with(compact('controller', 'action', 'currentUrl', 'roleId','userId','isAjaxRequest'));
            }
        });

        /* Custom Validator for PHONE Number */
        Validator::extend('phone_number', function($attribute, $value, $parameters)
        {
            // if (!preg_match("/(01)[0-9]{9}/",$value)) {
            if ((!preg_match("/^[0-9]*$/",$value)) || (strlen($value) >11) ) {
                return false;
            }
            return true;
        });

        /* Custom Validator for Double Number */
        Validator::extend('float', function($attribute, $value, $parameters)
        {

            if ( (!preg_match("/^[0-9.]*$/",$value)) || ($value == ".") || (substr_count($value,".") > 1 ) ) {
                return false;
            }
            return true;
        });

        /* Custom Validator for Double Number */
        Validator::extend('float_twodigit', function($attribute, $value, $parameters)
        {

            if ( (!preg_match("/^[0-9]*\.[0-9][0-9]$/",$value))) {
                return false;
            }
            return true;
        });

        

        /* Custom Validator for Date dd-mm-YYYY */
        Validator::extend('custom_date', function($attribute, $value, $parameters)
        {
            if(preg_match("/^[0-9]{1,2}-[0-9]{1,2}-[0-9]{4}$/", $value) === 0) {
                return false;
            } else {
                list($dd,$mm,$yyyy) = explode('-',$value);
                if (!checkdate($mm,$dd,$yyyy)) {
                    return false;
                }
            }

            return true;
        });
        /* Custom Validator for Mime Except Check */
        Validator::extend('mimes_except', function($attribute, $value, $parameters)
        {
            if (empty($value)) {
                return true;
            }

            $mimeList = array(
                'exe' => 'application/x-msdownload',
                'ext' => 'application/vnd.novadigm.ext',
                'bat' => 'application/x-msdownload',
                'msi' => 'application/x-msdownload',
                'dll' => 'application/x-msdownload',
                'dmg' => 'application/octet-stream',
            );

            $fileMimeType = $value->getClientMimeType();

            if (!empty($parameters)) {
                foreach ($parameters as $key => $value) {
                    if (!empty($mimeList[$value])) {
                        $preventMime = $mimeList[$value];
                        if ($preventMime == $fileMimeType) {
                            return false;
                        }
                    }
                }
            } else {
                return true;
            }
            
            return true;
        });

        /* Custom Validator for Mime Except Check */
        Validator::extend('fixed_len', function($attribute, $value, $parameters)
        {
            if (empty($value)) {
                return true;
            }
            $successFlag = 0;
            $valueLength = strlen($value);

            if (!empty($parameters)) {
                foreach ($parameters as $key => $params) {
                    if ($valueLength == $params) {
                        $successFlag = 1;
                        break;
                    }
                }
                if ($successFlag == 1) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
            
            return true;
        });
    }
}
