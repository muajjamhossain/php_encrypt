<?php
/**
 * User:Tanay Kumar Roy
 * Email:tanayroy12@gmail.com
 * Created by Tanay Kumar Roy<tanayroy12@gmail.com> on 3/30/2020.
 */

namespace App;


use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    protected $table = 'log_activities';
    protected $primaryKey = 'log_activity_id';
    protected $fillable = [
        'action',
        'model',
        'message',
        'previous',
        'updated',
        'user_id'
    ];

}