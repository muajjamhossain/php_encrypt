<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 3/30/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table='settings';
    protected $primaryKey = 'setting_id';
    protected $fillable = [
            'session_lifetime',
            'password_change_time',
            'allow_ip_restriction',
            'sla_blink',
            'sla_email_time',
            'forward_time',
			'noncustomersms'
    ];
}
