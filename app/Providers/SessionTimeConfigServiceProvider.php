<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 4/2/2020.
 */

namespace App\Providers;


use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SessionTimeConfigServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $config =array(
            'lifetime'=>1
        );
        $lifetime = 10;
        if(Schema::hasTable('settings')){
            $session_time = DB::table('settings')->first();
            if($session_time) {
                $lifetime = $session_time->session_lifetime;
            }
        }

        Config::set('session.lifetime', $lifetime);
        /*if (Schema::hasTable('mails')) {
            $mail = DB::table('mails')->first();
            if ($mail) //checking if table is not empty
            {
                $config = array(
                    'driver'     => $mail->driver,
                    'host'       => $mail->host,
                    'port'       => $mail->port,
                    'from'       => array('address' => $mail->from_address, 'name' => $mail->from_name),
                    'encryption' => $mail->encryption,
                    'username'   => $mail->username,
                    'password'   => $mail->password,
                    'sendmail'   => '/usr/sbin/sendmail -bs',
                    'pretend'    => false,
                );
                Config::set('mail', $config);
            }
        }*/
    }
}